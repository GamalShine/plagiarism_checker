import fs from 'node:fs';
import process from 'node:process';
import { PDFDocument, rgb } from 'pdf-lib';
import { getDocument } from 'pdfjs-dist/legacy/build/pdf.mjs';

function argument(name, fallback = '') {
    const index = process.argv.indexOf(name);
    return index >= 0 ? process.argv[index + 1] ?? fallback : fallback;
}

function normalize(value) {
    return String(value ?? '').replace(/\s+/g, ' ').trim().toLowerCase();
}

function searchVariants(value) {
    const original = String(value ?? '').replace(/\s+/g, ' ').trim();
    const words = original.split(' ').filter((word) => word.length > 2);
    const variants = [original];

    if (words.length >= 4) {
        variants.push(words.slice(0, 6).join(' '));
        if (words.length >= 10) {
            variants.push(words.slice(4, 10).join(' '));
        }
    }

    return [...new Set(variants.filter((variant) => variant.length >= 4))];
}

function color(value) {
    const match = String(value ?? '').trim().match(/^#?([0-9a-f]{6})$/i);
    if (!match) return { fill: rgb(0, 0, 0), transparent: true };
    const hex = match[1];
    return {
        fill: rgb(
            Number.parseInt(hex.slice(0, 2), 16) / 255,
            Number.parseInt(hex.slice(2, 4), 16) / 255,
            Number.parseInt(hex.slice(4, 6), 16) / 255,
        ),
        transparent: false,
    };
}

function boxesForRange(items, start, end) {
    const verticalOffset = 1.5;

    return items
        .filter((item) => item.end > start && item.start < end)
        .map((item) => {
            const overlapStart = Math.max(start, item.start);
            const overlapEnd = Math.min(end, item.end);
            const itemLength = Math.max(item.end - item.start, 1);
            const startRatio = (overlapStart - item.start) / itemLength;
            const endRatio = (overlapEnd - item.start) / itemLength;
            const x = item.x + item.width * startRatio;
            const width = Math.max(item.width * (endRatio - startRatio), 1);

            return {
                x,
                y: item.y - verticalOffset,
                width,
                height: item.height,
            };
        });
}

async function readPageMatches(sourceBytes, highlights, maxPages) {
    const pdf = await getDocument({ data: new Uint8Array(sourceBytes) }).promise;
    const matches = [];
    const searchableHighlights = highlights
        .flatMap((highlight) => searchVariants(highlight.text).map((variant) => ({
            ...highlight,
            normalizedText: normalize(variant),
        })))
        .filter((highlight) => highlight.normalizedText.length >= 4);
    const pageCount = Math.min(pdf.numPages, maxPages);

    for (let pageNumber = 1; pageNumber <= pageCount; pageNumber += 1) {
        const page = await pdf.getPage(pageNumber);
        const viewport = page.getViewport({ scale: 1 });
        const content = await page.getTextContent();
        const items = content.items
            .filter((item) => typeof item.str === 'string' && item.str.trim() !== '')
            .map((item) => {
                const transform = item.transform;
                const height = Math.max(Math.abs(transform[3]) || 0, item.height || 0, 6);
                return {
                    text: item.str,
                    start: 0,
                    end: 0,
                    x: transform[4],
                    y: viewport.height - transform[5] - height * 0.2,
                    width: Math.max(item.width || 0, 1),
                    height,
                };
            });

        const joined = items.map((item) => normalize(item.text)).filter(Boolean).join(' ');
        let offset = 0;
        for (const item of items) {
            item.text = normalize(item.text);
            if (!item.text) continue;
            item.start = offset;
            item.end = offset + item.text.length;
            offset = item.end + 1;
        }

        const normalizedPage = joined.toLowerCase();
        for (const highlight of searchableHighlights) {
            const needle = highlight.normalizedText;

            let start = normalizedPage.indexOf(needle);
            while (start >= 0) {
                const end = start + needle.length;
                const boxes = boxesForRange(items, start, end);

                if (boxes.length > 0) {
                    matches.push({
                        pageNumber,
                        boxes,
                        color: highlight.color || '#84CC16',
                        sourceIndex: Number.parseInt(highlight.source_index, 10) || 0,
                    });
                }

                start = normalizedPage.indexOf(needle, start + Math.max(needle.length, 1));
            }
        }
    }

    return matches;
}

async function copyInto(target, filePath) {
    if (!filePath || !fs.existsSync(filePath)) return [];
    const source = await PDFDocument.load(fs.readFileSync(filePath));
    const pages = await target.copyPages(source, source.getPageIndices());
    pages.forEach((page) => target.addPage(page));
    return pages;
}

async function main() {
    const output = process.argv[2];
    const manifest = {
        output,
        cover: argument('--cover'),
        source: argument('--source'),
        report: argument('--report'),
        highlights: argument('--highlights'),
        maxPages: Number.parseInt(argument('--max-pages', '200'), 10),
    };

    if (!output || !manifest.cover || !manifest.source || !manifest.report) {
        throw new Error('Required arguments: output, --cover, --source, --report');
    }

    const target = await PDFDocument.create();
    const highlights = manifest.highlights && fs.existsSync(manifest.highlights)
        ? JSON.parse(fs.readFileSync(manifest.highlights, 'utf8'))
        : [];

    await copyInto(target, manifest.cover);

    let highlightCount = 0;
    if (manifest.source && fs.existsSync(manifest.source)) {
        const sourceBytes = fs.readFileSync(manifest.source);
        const sourceDocument = await PDFDocument.load(sourceBytes);
        const maxPages = Number.isFinite(manifest.maxPages) && manifest.maxPages > 0
            ? manifest.maxPages
            : sourceDocument.getPageCount();
        const sourcePageIndices = sourceDocument
            .getPageIndices()
            .slice(0, maxPages);
        const matches = await readPageMatches(sourceBytes, highlights, maxPages);
        const sourcePages = await target.copyPages(sourceDocument, sourcePageIndices);

        for (const match of matches) {
            const page = sourcePages[match.pageNumber - 1];
            if (!page) continue;
            const parsedColor = color(match.color);
            if (parsedColor.transparent) continue;
            const fill = parsedColor.fill;
            for (const box of match.boxes) {
                page.drawRectangle({
                    x: box.x,
                    y: page.getHeight() - box.y - box.height,
                    width: box.width,
                    height: box.height,
                    color: fill,
                    opacity: 0.35,
                    borderColor: fill,
                    borderOpacity: 0.35,
                    borderWidth: 0.5,
                });
            }

            if (match.sourceIndex > 0 && match.boxes[0]) {
                const firstBox = match.boxes[0];
                const labelWidth = 11;
                const labelHeight = 10;
                const labelX = Math.max(0, firstBox.x - labelWidth - 2);
                const textBottom = page.getHeight() - firstBox.y - firstBox.height;
                const labelY = Math.max(
                    0,
                    Math.min(
                        page.getHeight() - labelHeight,
                        textBottom + (firstBox.height - labelHeight) / 2,
                    ),
                );
                page.drawRectangle({
                    x: labelX,
                    y: labelY,
                    width: labelWidth,
                    height: labelHeight,
                    color: fill,
                    opacity: 0.95,
                });
                page.drawText(String(match.sourceIndex), {
                    x: labelX + 2.5,
                    y: labelY + 2,
                    size: 7,
                    color: rgb(1, 1, 1),
                });
            }
            highlightCount += 1;
        }

        sourcePages.forEach((page) => target.addPage(page));
    }

    await copyInto(target, manifest.report);
    fs.writeFileSync(output, await target.save({ useObjectStreams: false }));
    process.stdout.write(JSON.stringify({ output, highlights: highlightCount, pages: target.getPageCount() }));
}

main().catch((error) => {
    process.stderr.write(`${error.stack ?? error}\n`);
    process.exitCode = 1;
});
