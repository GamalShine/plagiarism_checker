#!/usr/bin/env python3
"""Merge cover, source document pages, and report PDFs into one export file."""

import argparse
import json
import re
import sys
from pathlib import Path

try:
    import pymupdf
except ImportError:
    print(json.dumps({"error": "pymupdf is not installed. Run: pip install pymupdf"}))
    sys.exit(1)


def parse_color(value: str | None) -> tuple[float, float, float]:
    if not isinstance(value, str):
        return (1.0, 0.0, 0.0)

    match = re.fullmatch(r"#?([0-9a-fA-F]{6})", value.strip())
    if not match:
        return (1.0, 0.0, 0.0)

    color = match.group(1)
    return tuple(int(color[index:index + 2], 16) / 255 for index in (0, 2, 4))


def add_highlight_label(page: pymupdf.Page, rect: pymupdf.Rect, number: int, color: tuple[float, float, float]) -> None:
    label_width = max(10, 6 + len(str(number)) * 4)
    label_height = 10
    label_rect = pymupdf.Rect(
        rect.x0,
        max(0, rect.y0 - label_height),
        rect.x0 + label_width,
        max(label_height, rect.y0),
    )
    page.draw_rect(label_rect, color=color, fill=color, width=0, overlay=True)
    page.insert_text(
        (label_rect.x0 + 2, label_rect.y1 - 2),
        str(number),
        fontsize=7,
        fontname="hebo",
        color=(1, 1, 1),
        overlay=True,
    )


def find_highlight_quads(page: pymupdf.Page, highlights: list[dict]) -> list[tuple[list[pymupdf.Quad], int, tuple[float, float, float]]]:
    matches = []

    for item in highlights:
        text = str(item.get("text") or "").strip()
        if len(text) < 4:
            continue

        variants = [text]
        collapsed = re.sub(r"\s+", " ", text)
        if collapsed != text:
            variants.append(collapsed)

        quads = []
        for variant in variants:
            try:
                quads = page.search_for(variant, quads=True)
            except Exception:
                quads = []
            if quads:
                break

        if quads:
            matches.append((quads, int(item.get("source_index") or 0), parse_color(item.get("color"))))

    return matches


def highlight_source_pages(source: pymupdf.Document, highlights: list[dict], draw_labels: bool = True) -> int:
    applied = 0

    for page in source:
        for quads, source_index, color in find_highlight_quads(page, highlights):
            annotation = page.add_highlight_annot(quads)
            annotation.set_colors(stroke=color)
            annotation.set_opacity(0.35)
            annotation.update()
            if draw_labels and source_index > 0:
                add_highlight_label(page, quads[0].rect, source_index, color)
            applied += 1

    return applied


def load_highlights(manifest_path: str) -> list[dict]:
    if not manifest_path:
        return []

    try:
        with open(manifest_path, encoding="utf-8") as handle:
            payload = json.load(handle)
    except (OSError, json.JSONDecodeError):
        return []

    return payload if isinstance(payload, list) else []


def insert_pdf(
    target: pymupdf.Document,
    source_path: str,
    max_pages: int | None = None,
    highlights: list[dict] | None = None,
) -> int:
    source = pymupdf.open(source_path)
    page_count = source.page_count

    if max_pages is not None:
        page_count = min(page_count, max_pages)

    if page_count <= 0:
        source.close()
        return 0

    if highlights:
        highlight_source_pages(source, highlights, draw_labels=False)

    target.insert_pdf(source, from_page=0, to_page=page_count - 1)
    source.close()
    return page_count


def insert_pdf_as_images(
    target: pymupdf.Document,
    source_path: str,
    max_pages: int | None = None,
    highlights: list[dict] | None = None,
    jpeg_quality: int = 70,
    dpi: int = 96,
) -> int:
    source = pymupdf.open(source_path)
    page_count = source.page_count

    if max_pages is not None:
        page_count = min(page_count, max_pages)

    if page_count <= 0:
        source.close()
        return 0

    if highlights:
        highlight_source_pages(source, highlights)

    inserted = 0
    scale = max(72, dpi) / 72
    matrix = pymupdf.Matrix(scale, scale)

    try:
        for page_number in range(page_count):
            source_page = source[page_number]
            pix = source_page.get_pixmap(matrix=matrix, alpha=False, annots=True)
            page = target.new_page(width=source_page.rect.width, height=source_page.rect.height)
            page.insert_image(page.rect, stream=pix.tobytes("jpeg", jpeg_quality))
            for quads, source_index, color in find_highlight_quads(source_page, highlights or []):
                if source_index > 0:
                    page.insert_text(
                        (quads[0].rect.x0, max(8, quads[0].rect.y0 - 2)),
                        str(source_index),
                        fontsize=7,
                        fontname="hebo",
                        color=color,
                        overlay=True,
                    )
            inserted += 1
    finally:
        source.close()

    return inserted


def load_pixmap(image_path: Path) -> pymupdf.Pixmap | None:
    try:
        pix = pymupdf.Pixmap(str(image_path))
    except Exception:
        return None

    if pix.n - pix.alpha >= 4:
        rgb = pymupdf.Pixmap(pymupdf.csRGB, pix)
        pix = None
        return rgb

    return pix


def normalize_pages(payload) -> list[dict]:
    if isinstance(payload, list):
        pages = []
        for item in payload:
            if isinstance(item, str):
                pages.append({"path": item})
            elif isinstance(item, dict) and item.get("path"):
                pages.append(item)
        return pages

    if isinstance(payload, dict):
        if isinstance(payload.get("pages"), list):
            return normalize_pages(payload["pages"])
        if isinstance(payload.get("images"), list):
            return normalize_pages(payload["images"])

    return []


def insert_images(
    target: pymupdf.Document,
    pages: list[dict],
    max_pages: int | None = None,
    jpeg_quality: int = 85,
) -> int:
    inserted = 0

    for page_info in pages:
        if max_pages is not None and inserted >= max_pages:
            break

        path = Path(str(page_info.get("path", "")))
        if not path.is_file():
            continue

        pix = load_pixmap(path)
        if pix is None:
            continue

        try:
            width = float(page_info.get("width") or pix.width)
            height = float(page_info.get("height") or pix.height)
            page = target.new_page(width=width, height=height)
            jpeg_bytes = pix.tobytes("jpeg", jpeg_quality)
            page.insert_image(page.rect, stream=jpeg_bytes)
            inserted += 1
        except Exception:
            continue
        finally:
            pix = None

    return inserted


def load_image_paths(manifest_path: str) -> list[dict]:
    with open(manifest_path, encoding="utf-8") as handle:
        payload = json.load(handle)

    return normalize_pages(payload)


def add_cover_footer(document: pymupdf.Document, submission_id: str) -> None:
    if document.page_count == 0:
        return

    page = document[0]
    header_y = 20
    footer_y = page.rect.height - 20
    font_size = 8
    left_text = f"Page 1 of {document.page_count} - Cover Page"
    right_text = f"Submission ID {submission_id}"
    color = (0.35, 0.35, 0.35)

    right_width = pymupdf.get_text_length(right_text, fontname="helv", fontsize=font_size)
    right_x = page.rect.width - right_width - 28
    page.insert_text((28, header_y), left_text, fontsize=font_size, fontname="helv", color=color, overlay=True)
    page.insert_text(
        (right_x, header_y),
        right_text,
        fontsize=font_size,
        fontname="helv",
        color=color,
        overlay=True,
    )
    page.insert_text((28, footer_y), left_text, fontsize=font_size, fontname="helv", color=color, overlay=True)
    page.insert_text(
        (right_x, footer_y),
        right_text,
        fontsize=font_size,
        fontname="helv",
        color=color,
        overlay=True,
    )


def main() -> int:
    parser = argparse.ArgumentParser()
    parser.add_argument("output_pdf")
    parser.add_argument("--cover", default="")
    parser.add_argument("--source", default="")
    parser.add_argument("--source-as-images", action="store_true")
    parser.add_argument("--source-images", default="")
    parser.add_argument("--highlights", default="")
    parser.add_argument("--report", default="")
    parser.add_argument("--max-pages", type=int, default=200)
    parser.add_argument("--jpeg-quality", type=int, default=70)
    parser.add_argument("--source-dpi", type=int, default=96)
    parser.add_argument("--submission-id", default="")
    args = parser.parse_args()

    jpeg_quality = max(40, min(args.jpeg_quality, 95))
    final = pymupdf.open()
    highlights = load_highlights(args.highlights)

    try:
        inserted = {
            "cover_pages": 0,
            "source_pages": 0,
            "report_pages": 0,
        }

        if args.cover:
            inserted["cover_pages"] = insert_pdf(final, args.cover)

        if args.source_images:
            pages = load_image_paths(args.source_images)
            inserted["source_pages"] = insert_images(
                final,
                pages,
                args.max_pages,
                jpeg_quality,
            )
        elif args.source:
            if args.source_as_images:
                inserted["source_pages"] = insert_pdf_as_images(
                    final,
                    args.source,
                    args.max_pages,
                    highlights,
                    jpeg_quality,
                    args.source_dpi,
                )
            else:
                inserted["source_pages"] = insert_pdf(
                    final,
                    args.source,
                    args.max_pages,
                    highlights,
                )

        if args.report:
            inserted["report_pages"] = insert_pdf(final, args.report)

        if args.submission_id:
            add_cover_footer(final, args.submission_id)

        if final.page_count == 0:
            print(json.dumps({"error": "No pages to export"}))
            return 1

        final.save(
            args.output_pdf,
            garbage=4,
            deflate=True,
            deflate_images=True,
            deflate_fonts=True,
        )
        print(json.dumps({
            "output": args.output_pdf,
            "total_pages": final.page_count,
            "jpeg_quality": jpeg_quality,
            "highlights": len(highlights),
            **inserted,
        }))
        return 0
    finally:
        final.close()


if __name__ == "__main__":
    raise SystemExit(main())
