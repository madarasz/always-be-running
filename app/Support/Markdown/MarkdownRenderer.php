<?php

namespace App\Support\Markdown;

use Parsedown;

class MarkdownRenderer
{
    /**
     * Parsedown parser instance.
     *
     * @var \Parsedown
     */
    protected $parser;

    /**
     * Create a new markdown renderer instance.
     *
     * @param  \Parsedown|null  $parser
     */
    public function __construct(Parsedown $parser = null)
    {
        $this->parser = $parser ?: new Parsedown();
    }

    /**
     * Convert markdown to HTML.
     *
     * @param  string|null  $markdown
     * @return string
     */
    public function convertToHtml($markdown)
    {
        return $this->parser->text((string) $markdown);
    }
}
