<?php

declare(strict_types=1);
require 'vendor/autoload.php';
use PHPUnit\Framework\TestCase;

final class PostgrestTest extends TestCase
{
    private $client;

    public function setup(): void
    {
        parent::setUp();
        \Dotenv\Dotenv::createImmutable(__DIR__);
        $dotenv = \Dotenv\Dotenv::createUnsafeImmutable(__DIR__, '/../../.env.test');
        $dotenv->load();
        $api_key = getenv('API_KEY');
        $reference_id = getenv('REFERENCE_ID');
        $scheme = 'https://';
        $domain = '.supabase.co/';
        $path = 'rest/v1/';
        $opts = [];
        $this->client = new PostgrestClient($reference_id, $api_key, $opts, $domain, $scheme, $path);
    }
}
