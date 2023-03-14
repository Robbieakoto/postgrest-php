<?php

declare(strict_types=1);
require 'vendor/autoload.php';
use PHPUnit\Framework\TestCase;

final class PostgrestClientTest extends TestCase
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

    /**
     * Test Retrieves the details of all Storage buckets within an existing project function.
     *
     * @return void
     */
    public function testFetchData(): void
    {
        $result = $this->client->from('countries')->select()->execute();
        $this->assertEquals('200', $result->status);
        $this->assertEquals('OK', $result->statusText);
    }

    /**
     * Test Creates a new Storage bucket function.
     *
     * @return void
     */
    public function testCreateBucket(): void
    {
        $bucketName = 'bucket'.microtime(false);
        $result = $this->client->createBucket($bucketName, ['public' => true]);
        $this->assertEquals('200', $result->getStatusCode());
        $this->assertEquals('OK', $result->getReasonPhrase());
        $getValue = json_decode((string) $result->getBody());
        $obj = $getValue->{'name'};
        $this->assertEquals($bucketName, $obj);
        $result = $this->client->deleteBucket($bucketName);
    }

    /**
     * Test Retrieves the details of an existing Storage bucket function.
     *
     * @return void
     */
    public function testGetBucketWithId(): void
    {
        $bucketName = 'bucket'.microtime(false);
        $this->client->createBucket($bucketName, ['public' => true]);
        $bucket = $this->client->getBucket($bucketName);
        $this->assertEquals('200', $bucket->getStatusCode());
        $this->assertEquals('OK', $bucket->getReasonPhrase());
        $getValue = json_decode((string) $bucket->getBody());
        $obj = $getValue->{'id'};
        $this->assertEquals($bucketName, $obj);
        $this->client->deleteBucket($bucketName);
    }

    /**
     * Test Updates a Storage bucket function.
     *
     * @return void
     */
    public function testUpdateBucket(): void
    {
        $bucketName = 'bucket'.microtime(false);
        $result = $this->client->createBucket($bucketName, ['public' => true]);
        $result = $this->client->updateBucket($bucketName, ['public' => true]);
        $this->assertEquals('200', $result->getStatusCode());
        $this->assertEquals('OK', $result->getReasonPhrase());
        $this->assertJsonStringEqualsJsonString('{"message":"Successfully updated"}', (string) $result->getBody());
        $result = $this->client->deleteBucket($bucketName);
    }

    /**
     * Test Removes all objects inside a single bucket function.
     *
     * @return void
     */
    public function testEmptyBucket()
    {
        $bucketName = 'bucket'.microtime(false);
        $result = $this->client->createBucket($bucketName, ['public' => true]);
        $result = $this->client->emptyBucket($bucketName);
        $this->assertEquals('200', $result->getStatusCode());
        $this->assertEquals('OK', $result->getReasonPhrase());
        $this->assertJsonStringEqualsJsonString('{"message":"Successfully emptied"}', (string) $result->getBody());
        $result = $this->client->deleteBucket($bucketName);
    }

    /**
     * Test Deletes an existing bucket function.
     *
     * @return void
     */
    public function testDeleteBucket()
    {
        $bucketName = 'bucket'.microtime(false);
        $result = $this->client->createBucket($bucketName, ['public' => true]);
        $result = $this->client->deleteBucket($bucketName);
        $this->assertEquals('200', $result->getStatusCode());
        $this->assertEquals('OK', $result->getReasonPhrase());
        $this->assertJsonStringEqualsJsonString('{"message":"Successfully deleted"}', (string) $result->getBody());
    }

    /**
     * Test Invailid bucket id function.
     *
     * @return void
     */
    public function testGetBucketWithInvalidId(): void
    {
        try {
            $this->client->getBucket('not-a-real-bucket-id');
        } catch (\Exception $e) {
            $this->assertEquals('The resource was not found', $e->getMessage());
        }
    }

    /**
     * Test Creates a new Storage public bucket function.
     *
     * @return void
     */
    public function testCreatePrivateBucket(): void
    {
        $bucketName = 'bucket'.microtime(false);
        $result = $this->client->createBucket($bucketName, ['public' => false]);
        $this->assertEquals('200', $result->getStatusCode());
        $this->assertEquals('OK', $result->getReasonPhrase());
        $this->assertJsonStringEqualsJsonString('{"name":"'.$bucketName.'"}', (string) $result->getBody());
        $resultInfo = $this->client->getBucket($bucketName);
        $getValue = json_decode((string) $resultInfo->getBody());
        $isPrivate = $getValue->{'public'};
        $this->assertFalse($isPrivate);
        $result = $this->client->deleteBucket($bucketName);
    }
}
