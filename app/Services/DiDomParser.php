<?php

namespace App\Services;

use DiDom\Document;

class DiDomParser
{
    private Document $document;

    public function __construct()
    {
        $this->document = new Document();
    }
    public function parseUrl(string $url): array
    {
        $this->document->loadHtmlFile($url);

        return $this->parseDocument();
    }

    public function parseHtml(string $html): array
    {
        $this->document->loadHtml($html);

        return $this->parseDocument();
    }

    private function parseDocument(): array
    {
        return [
            'h1' => $this->document->find('h1')[0]?->text(),
            'title' => $this->document->find('title')[0]?->text(),
            'content' => $this->document->find('meta[name=description][content]')[0]?->attr('content'),
        ];
    }
}
