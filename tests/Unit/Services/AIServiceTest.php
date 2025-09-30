<?php

declare(strict_types=1);

namespace Modules\AI\Tests\Unit\Services;

use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Modules\AI\Services\AIService;
use Tests\TestCase;

class AIServiceTest extends TestCase
{
    use RefreshDatabase;

    protected AIService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AIService;
    }

    public function test_generates_text_completion(): void
    {
        // Arrange
        $prompt = 'Complete this sentence: The weather today is';
        $maxTokens = 50;

        // Act
        $result = $this->service->generateTextCompletion($prompt, $maxTokens);

        // Assert
        $this->assertIsString($result);
        $this->assertNotEmpty($result);
        $this->assertStringContainsString($prompt, $result);
    }

    public function test_generates_text_with_temperature(): void
    {
        // Arrange
        $prompt = 'Write a creative story about a robot';
        $temperature = 0.8; // High creativity

        // Act
        $result = $this->service->generateText($prompt, $temperature);

        // Assert
        $this->assertIsString($result);
        $this->assertNotEmpty($result);
        $this->assertStringContainsString('robot', $result);
    }

    public function test_validates_temperature_range(): void
    {
        // Arrange
        $prompt = 'Test prompt';
        $invalidTemperature = 2.0; // Out of range

        // Act & Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Temperature must be between 0.0 and 1.0');

        $this->service->generateText($prompt, $invalidTemperature);
    }

    public function test_validates_max_tokens(): void
    {
        // Arrange
        $prompt = 'Test prompt';
        $invalidMaxTokens = -10; // Negative value

        // Act & Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Max tokens must be positive');

        $this->service->generateText($prompt, 0.5, $invalidMaxTokens);
    }

    public function test_generates_code_completion(): void
    {
        // Arrange
        $codePrompt = 'function calculateSum(';
        $language = 'php';

        // Act
        $result = $this->service->generateCodeCompletion($codePrompt, $language);

        // Assert
        $this->assertIsString($result);
        $this->assertNotEmpty($result);
        $this->assertStringContainsString('function calculateSum(', $result);
    }

    public function test_validates_programming_language(): void
    {
        // Arrange
        $codePrompt = 'function test()';
        $invalidLanguage = 'invalid_lang';

        // Act & Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported programming language: invalid_lang');

        $this->service->generateCodeCompletion($codePrompt, $invalidLanguage);
    }

    public function test_analyzes_sentiment(): void
    {
        // Arrange
        $text = 'I am very happy with this product!';

        // Act
        $result = $this->service->analyzeSentiment($text);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('sentiment', $result);
        $this->assertArrayHasKey('score', $result);
        $this->assertArrayHasKey('confidence', $result);
        $this->assertContains($result['sentiment'], ['positive', 'negative', 'neutral']);
        $this->assertGreaterThanOrEqual(0.0, $result['score']);
        $this->assertLessThanOrEqual(1.0, $result['score']);
    }

    public function test_extracts_entities(): void
    {
        // Arrange
        $text = 'Apple Inc. was founded by Steve Jobs in Cupertino, California.';

        // Act
        $result = $this->service->extractEntities($text);

        // Assert
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);

        // Verifica che le entità siano estratte correttamente
        $entityNames = array_column($result, 'name');
        $this->assertContains('Apple Inc.', $entityNames);
        $this->assertContains('Steve Jobs', $entityNames);
        $this->assertContains('Cupertino', $entityNames);
        $this->assertContains('California', $entityNames);
    }

    public function test_classifies_text(): void
    {
        // Arrange
        $text = 'This is a technical support request about server issues.';
        $categories = ['support', 'sales', 'billing', 'technical'];

        // Act
        $result = $this->service->classifyText($text, $categories);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('category', $result);
        $this->assertArrayHasKey('confidence', $result);
        $this->assertContains($result['category'], $categories);
        $this->assertGreaterThanOrEqual(0.0, $result['confidence']);
        $this->assertLessThanOrEqual(1.0, $result['confidence']);
    }

    public function test_validates_categories_not_empty(): void
    {
        // Arrange
        $text = 'Test text';
        $emptyCategories = [];

        // Act & Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Categories list cannot be empty');

        $this->service->classifyText($text, $emptyCategories);
    }

    public function test_translates_text(): void
    {
        // Arrange
        $text = 'Hello, how are you?';
        $sourceLanguage = 'en';
        $targetLanguage = 'it';

        // Act
        $result = $this->service->translateText($text, $sourceLanguage, $targetLanguage);

        // Assert
        $this->assertIsString($result);
        $this->assertNotEmpty($result);
        $this->assertNotEquals($text, $result); // Should be translated
    }

    public function test_validates_language_codes(): void
    {
        // Arrange
        $text = 'Test text';
        $invalidSourceLang = 'invalid';
        $targetLang = 'it';

        // Act & Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid source language code: invalid');

        $this->service->translateText($text, $invalidSourceLang, $targetLang);
    }

    public function test_generates_image_description(): void
    {
        // Arrange
        $imagePath = '/path/to/image.jpg';

        // Act
        $result = $this->service->generateImageDescription($imagePath);

        // Assert
        $this->assertIsString($result);
        $this->assertNotEmpty($result);
    }

    public function test_validates_image_file_exists(): void
    {
        // Arrange
        $nonExistentImage = '/path/to/nonexistent.jpg';

        // Act & Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Image file not found: /path/to/nonexistent.jpg');

        $this->service->generateImageDescription($nonExistentImage);
    }

    public function test_fine_tunes_model(): void
    {
        // Arrange
        $trainingData = [
            ['prompt' => 'Input 1', 'completion' => 'Output 1'],
            ['prompt' => 'Input 2', 'completion' => 'Output 2'],
        ];
        $modelName = 'gpt-3.5-turbo';

        // Act
        $result = $this->service->fineTuneModel($trainingData, $modelName);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('job_id', $result);
        $this->assertArrayHasKey('status', $result);
        $this->assertArrayHasKey('model_name', $result);
        $this->assertEquals($modelName, $result['model_name']);
    }

    public function test_validates_training_data_format(): void
    {
        // Arrange
        $invalidTrainingData = [
            ['prompt' => 'Input 1'], // Missing completion
            ['completion' => 'Output 2'], // Missing prompt
        ];
        $modelName = 'gpt-3.5-turbo';

        // Act & Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Training data must have both prompt and completion');

        $this->service->fineTuneModel($invalidTrainingData, $modelName);
    }

    public function test_gets_fine_tuning_status(): void
    {
        // Arrange
        $jobId = 'ft-1234567890';

        // Act
        $result = $this->service->getFineTuningStatus($jobId);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('job_id', $result);
        $this->assertArrayHasKey('status', $result);
        $this->assertEquals($jobId, $result['job_id']);
    }

    public function test_validates_job_id_format(): void
    {
        // Arrange
        $invalidJobId = 'invalid-job-id';

        // Act & Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid job ID format: invalid-job-id');

        $this->service->getFineTuningStatus($invalidJobId);
    }

    public function test_handles_api_errors_gracefully(): void
    {
        // Arrange
        $prompt = 'Test prompt';

        // Mock API error response
        $this->mock('Modules\AI\Services\OpenAIAPIService', function ($mock) {
            $mock->shouldReceive('generateText')
                ->andThrow(new Exception('API rate limit exceeded'));
        });

        // Act & Assert
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('API rate limit exceeded');

        $this->service->generateText($prompt);
    }

    public function test_implements_rate_limiting(): void
    {
        // Arrange
        $prompt = 'Test prompt';

        // Act
        $startTime = microtime(true);
        $result1 = $this->service->generateText($prompt);
        $result2 = $this->service->generateText($prompt);
        $endTime = microtime(true);

        // Assert
        $this->assertIsString($result1);
        $this->assertIsString($result2);

        // Verifica che non ci siano chiamate troppo rapide
        $executionTime = $endTime - $startTime;
        $this->assertGreaterThan(0.1, $executionTime, 'Rate limiting should prevent too rapid calls');
    }

    public function test_caches_results(): void
    {
        // Arrange
        $prompt = 'Test prompt for caching';

        // Act - First call
        $result1 = $this->service->generateText($prompt);

        // Second call (should use cache)
        $result2 = $this->service->generateText($prompt);

        // Assert
        $this->assertEquals($result1, $result2);
        $this->assertIsString($result1);
    }

    public function test_respects_cache_ttl(): void
    {
        // Arrange
        $prompt = 'Test prompt for TTL';
        $ttl = 60; // 1 minute

        // Act
        $result1 = $this->service->generateText($prompt, 0.5, 100, $ttl);

        // Simulate time passing
        $this->travel(61)->seconds();

        // Second call (cache expired)
        $result2 = $this->service->generateText($prompt, 0.5, 100, $ttl);

        // Assert
        $this->assertIsString($result1);
        $this->assertIsString($result2);
        // Results might be different due to cache expiration
    }

    public function test_logs_api_calls(): void
    {
        // Arrange
        $prompt = 'Test prompt for logging';

        // Act
        $result = $this->service->generateText($prompt);

        // Assert
        $this->assertIsString($result);

        // Verifica che la chiamata sia stata loggata
        $this->assertDatabaseHas('ai_api_logs', [
            'prompt' => $prompt,
            'response_length' => strlen($result),
            'status' => 'success',
        ]);
    }

    public function test_tracks_usage_metrics(): void
    {
        // Arrange
        $prompt = 'Test prompt for metrics';

        // Act
        $result = $this->service->generateText($prompt);

        // Assert
        $this->assertIsString($result);

        // Verifica che le metriche siano aggiornate
        $this->assertDatabaseHas('ai_usage_metrics', [
            'date' => now()->toDateString(),
            'requests_count' => 1,
            'tokens_used' => strlen($result),
        ]);
    }
}
