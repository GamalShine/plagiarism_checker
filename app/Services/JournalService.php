<?php

namespace App\Services;

use App\Models\Journal;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Style\Font;
use Illuminate\Support\Facades\Storage;

class JournalService
{
    public function generate(Journal $journal): Journal
    {
        // Generate PDF
        $pdfPath = $this->generatePdf($journal);

        // Generate DOCX
        $docxPath = $this->generateDocx($journal);

        $journal->update([
            'file_path_pdf'  => $pdfPath,
            'file_path_docx' => $docxPath,
            'status'         => 'generated',
        ]);

        return $journal->fresh();
    }

    private function generatePdf(Journal $journal): string
    {
        $view = match ($journal->template_type) {
            'template_a' => 'journals.template_a_pdf',
            'template_b' => 'journals.template_b_pdf',
            default      => 'journals.academic_template_pdf',
        };

        $pdf = Pdf::loadView($view, compact('journal'))
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => false,
            ]);

        $filename = 'journals/pdf/' . $journal->id . '_' . time() . '.pdf';
        Storage::disk('public')->put($filename, $pdf->output());

        return $filename;
    }

    private function generateDocx(Journal $journal): string
    {
        $phpWord = new PhpWord();

        // Document properties
        $phpWord->getDocInfo()->setTitle($journal->title);
        $phpWord->getDocInfo()->setCreator($journal->author);

        // Add default styles based on template
        if (in_array($journal->template_type, ['template_b', 'doaj'])) {
            $phpWord->setDefaultFontName('Arial');
        } else {
            $phpWord->setDefaultFontName('Times New Roman');
        }
        $phpWord->setDefaultFontSize(11);

        $section = $phpWord->addSection([
            'marginTop'    => 1440,
            'marginBottom' => 1440,
            'marginLeft'   => 1440,
            'marginRight'  => 1440,
        ]);

        if ($journal->template_type === 'template_b') {
            $this->buildTemplateB($section, $journal);
        } else {
            $this->buildTemplateA($section, $journal);
        }

        $filename = 'journals/docx/' . $journal->id . '_' . time() . '.docx';
        $fullPath = storage_path('app/public/' . $filename);

        // Ensure directory exists
        if (!file_exists(dirname($fullPath))) {
            mkdir(dirname($fullPath), 0777, true);
        }

        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($fullPath);

        return $filename;
    }

    private function buildTemplateA($section, Journal $journal): void
    {
        // Title
        $section->addText($journal->title, [
            'bold' => true, 'size' => 16, 'name' => 'Times New Roman'
        ], ['alignment' => 'center', 'spaceAfter' => 200]);

        // Author
        $section->addText($journal->author, [
            'size' => 12, 'name' => 'Times New Roman'
        ], ['alignment' => 'center', 'spaceAfter' => 100]);

        if ($journal->institution) {
            $section->addText($journal->institution, [
                'italic' => true, 'size' => 10, 'name' => 'Times New Roman'
            ], ['alignment' => 'center', 'spaceAfter' => 100]);
        }

        if ($journal->email) {
            $section->addText($journal->email, [
                'size' => 10, 'name' => 'Times New Roman'
            ], ['alignment' => 'center', 'spaceAfter' => 300]);
        }

        // Line
        $section->addTextBreak(1);

        // Abstract
        $section->addText('ABSTRACT', [
            'bold' => true, 'size' => 12, 'name' => 'Times New Roman'
        ], ['spaceAfter' => 100]);
        $section->addText($journal->abstract, [
            'italic' => true, 'size' => 11, 'name' => 'Times New Roman'
        ], ['spaceAfter' => 100]);

        // Keywords
        $section->addText('Keywords: ' . $journal->keywords, [
            'italic' => true, 'size' => 10, 'name' => 'Times New Roman'
        ], ['spaceAfter' => 300]);

        $section->addTextBreak(1);

        // Sections
        $sections = is_array($journal->content) ? $journal->content : json_decode($journal->content ?? '[]', true);
        foreach ($sections as $idx => $sec) {
            $section->addText(($idx + 1) . '. ' . strtoupper($sec['title'] ?? 'SECTION'), [
                'bold' => true, 'size' => 12, 'name' => 'Times New Roman'
            ], ['spaceAfter' => 100, 'spaceBefore' => 200]);

            $section->addText($sec['content'] ?? '', [
                'size' => 11, 'name' => 'Times New Roman'
            ], ['spaceAfter' => 200, 'lineHeight' => 1.5]);
        }
    }

    private function buildTemplateB($section, Journal $journal): void
    {
        // Modern style template
        $section->addText($journal->title, [
            'bold' => true, 'size' => 18, 'name' => 'Arial', 'color' => '1a1a2e'
        ], ['alignment' => 'center', 'spaceAfter' => 200]);

        $section->addText('by ' . $journal->author, [
            'size' => 13, 'name' => 'Arial', 'color' => '4a4a8a'
        ], ['alignment' => 'center', 'spaceAfter' => 100]);

        if ($journal->institution) {
            $section->addText($journal->institution, [
                'italic' => true, 'size' => 11, 'name' => 'Arial', 'color' => '666666'
            ], ['alignment' => 'center', 'spaceAfter' => 300]);
        }

        $section->addTextBreak(1);

        // Abstract box simulation
        $section->addText('ABSTRACT', [
            'bold' => true, 'size' => 13, 'name' => 'Arial', 'color' => '4a4a8a'
        ], ['spaceAfter' => 100]);

        $section->addText($journal->abstract, [
            'size' => 11, 'name' => 'Arial'
        ], ['spaceAfter' => 200]);

        $section->addText('Keywords: ' . $journal->keywords, [
            'italic' => true, 'bold' => true, 'size' => 10, 'name' => 'Arial', 'color' => '4a4a8a'
        ], ['spaceAfter' => 400]);

        // Sections
        $sections = is_array($journal->content) ? $journal->content : json_decode($journal->content ?? '[]', true);
        foreach ($sections as $idx => $sec) {
            $section->addText(($idx + 1) . '. ' . ($sec['title'] ?? 'Section'), [
                'bold' => true, 'size' => 14, 'name' => 'Arial', 'color' => '4a4a8a'
            ], ['spaceBefore' => 300, 'spaceAfter' => 100]);

            $section->addText($sec['content'] ?? '', [
                'size' => 11, 'name' => 'Arial'
            ], ['spaceAfter' => 200]);
        }
    }
}
