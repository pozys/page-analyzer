<?php

namespace  Pozys\PageAnalyzer\Services;

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
            'h1' => optional($this->document->first('h1'))->text(),
            'title' => optional($this->document->first('title'))->text(),
            'content' => optional($this->document->first('meta[name=description][content]'))->attr('content'),
        ];
    }
}
