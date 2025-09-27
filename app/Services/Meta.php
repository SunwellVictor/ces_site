<?php

namespace App\Services;

class Meta
{
    protected array $data = [
        'title' => '',
        'description' => '',
        'canonical' => '',
        'og' => [],
        'twitter' => [],
        'article' => [],
        'noindex' => false,
        'robots' => '',
    ];

    /**
     * Set the page title
     */
    public function title(string $title): self
    {
        $this->data['title'] = $title;
        return $this;
    }

    /**
     * Set the page description
     */
    public function description(string $description): self
    {
        $this->data['description'] = $description;
        return $this;
    }

    /**
     * Set the canonical URL
     */
    public function canonical(string $url = null): self
    {
        $this->data['canonical'] = $url ?? request()->url();
        return $this;
    }

    /**
     * Set Open Graph meta tags
     */
    public function og(array $kv): self
    {
        $this->data['og'] = array_merge($this->data['og'], $kv);
        return $this;
    }

    /**
     * Set Twitter meta tags
     */
    public function twitter(array $kv): self
    {
        $this->data['twitter'] = array_merge($this->data['twitter'], $kv);
        return $this;
    }

    /**
     * Set article meta tags
     */
    public function article(array $kv): self
    {
        $this->data['article'] = array_merge($this->data['article'], $kv);
        return $this;
    }

    /**
     * Set noindex flag for private pages
     */
    public function noindex(bool $noindex = true): self
    {
        $this->data['noindex'] = $noindex;
        return $this;
    }

    /**
     * Set robots meta tag
     */
    public function setRobots(string $robots): self
    {
        $this->data['robots'] = $robots;
        return $this;
    }

    /**
     * Get the title
     */
    public function getTitle(): string
    {
        return $this->data['title'];
    }

    /**
     * Get the description
     */
    public function getDescription(): string
    {
        return $this->data['description'];
    }

    /**
     * Get the canonical URL
     */
    public function getCanonical(): string
    {
        return $this->data['canonical'] ?: request()->url();
    }

    /**
     * Get Open Graph data
     */
    public function getOg(): array
    {
        return $this->data['og'];
    }

    /**
     * Get Twitter data
     */
    public function getTwitter(): array
    {
        return $this->data['twitter'];
    }

    /**
     * Get article data
     */
    public function getArticle(): array
    {
        return $this->data['article'];
    }

    /**
     * Check if page should be noindexed
     */
    public function isNoindex(): bool
    {
        return $this->data['noindex'];
    }

    /**
     * Get robots meta tag value
     */
    public function getRobots(): string
    {
        return $this->data['robots'];
    }

    /**
     * Render all meta data for Blade partial
     */
    public function renderHead(): array
    {
        // Set default OG values if not already set
        $og = array_merge([
            'title' => $this->getTitle(),
            'description' => $this->getDescription(),
            'type' => 'website',
            'url' => $this->getCanonical(),
        ], $this->data['og']);

        // Set default Twitter values if not already set
        $twitter = array_merge([
            'card' => 'summary',
            'title' => $this->getTitle(),
            'description' => $this->getDescription(),
        ], $this->data['twitter']);

        return [
            'title' => $this->getTitle(),
            'description' => $this->getDescription(),
            'canonical' => $this->getCanonical(),
            'og' => $og,
            'twitter' => $twitter,
            'article' => $this->getArticle(),
            'noindex' => $this->isNoindex(),
            'robots' => $this->getRobots(),
        ];
    }

    /**
     * Reset all meta data
     */
    public function reset(): self
    {
        $this->data = [
            'title' => '',
            'description' => '',
            'canonical' => '',
            'og' => [],
            'twitter' => [],
            'article' => [],
            'noindex' => false,
            'robots' => '',
        ];
        return $this;
    }
}