#!/usr/bin/env python3
"""Convert PDF pages to JPEG images while preserving original page dimensions."""

import argparse
import json
import sys

try:
    import pymupdf
except ImportError:
    print(json.dumps({"error": "pymupdf is not installed. Run: pip install pymupdf"}))
    sys.exit(1)


def main() -> int:
    parser = argparse.ArgumentParser()
    parser.add_argument("pdf_path")
    parser.add_argument("output_dir")
    parser.add_argument("--dpi", type=int, default=120)
    parser.add_argument("--jpeg-quality", type=int, default=85)
    parser.add_argument("--max-pages", type=int, default=200)
    args = parser.parse_args()

    jpeg_quality = max(40, min(args.jpeg_quality, 95))

    try:
        doc = pymupdf.open(args.pdf_path)
    except Exception as exc:
        print(json.dumps({"error": f"Failed to open PDF: {exc}"}))
        return 1

    total_pages = doc.page_count
    page_count = min(total_pages, args.max_pages)
    scale = args.dpi / 72.0
    matrix = pymupdf.Matrix(scale, scale)
    pages = []

    for index in range(page_count):
        page = doc.load_page(index)
        rect = page.rect
        pix = page.get_pixmap(matrix=matrix, alpha=False)
        filename = f"page_{index + 1:04d}.jpg"
        output_path = f"{args.output_dir.rstrip('/')}/{filename}"
        pix.save(output_path, jpg_quality=jpeg_quality)
        pix = None
        pages.append({
            "path": output_path,
            "width": float(rect.width),
            "height": float(rect.height),
        })

    doc.close()

    result = {
        "pages": pages,
        "images": [page["path"] for page in pages],
        "page_count": page_count,
        "total_pages": total_pages,
        "dpi": args.dpi,
        "jpeg_quality": jpeg_quality,
    }

    result_file = f"{args.output_dir.rstrip('/')}/conversion_result.json"
    with open(result_file, "w", encoding="utf-8") as handle:
        json.dump(result, handle)

    print(json.dumps(result))

    return 0


if __name__ == "__main__":
    raise SystemExit(main())
