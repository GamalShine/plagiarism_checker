#!/usr/bin/env python3
"""Merge cover, source document pages, and report PDFs into one export file."""

import argparse
import json
import sys
from pathlib import Path

try:
    import pymupdf
except ImportError:
    print(json.dumps({"error": "pymupdf is not installed. Run: pip install pymupdf"}))
    sys.exit(1)


def insert_pdf(target: pymupdf.Document, source_path: str, max_pages: int | None = None) -> int:
    source = pymupdf.open(source_path)
    page_count = source.page_count

    if max_pages is not None:
        page_count = min(page_count, max_pages)

    if page_count <= 0:
        source.close()
        return 0

    target.insert_pdf(source, from_page=0, to_page=page_count - 1)
    source.close()
    return page_count


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


def main() -> int:
    parser = argparse.ArgumentParser()
    parser.add_argument("output_pdf")
    parser.add_argument("--cover", default="")
    parser.add_argument("--source", default="")
    parser.add_argument("--source-images", default="")
    parser.add_argument("--report", default="")
    parser.add_argument("--max-pages", type=int, default=200)
    parser.add_argument("--jpeg-quality", type=int, default=85)
    args = parser.parse_args()

    jpeg_quality = max(40, min(args.jpeg_quality, 95))
    final = pymupdf.open()

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
            inserted["source_pages"] = insert_pdf(final, args.source, args.max_pages)

        if args.report:
            inserted["report_pages"] = insert_pdf(final, args.report)

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
            **inserted,
        }))
        return 0
    finally:
        final.close()


if __name__ == "__main__":
    raise SystemExit(main())
