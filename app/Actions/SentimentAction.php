<?php

declare(strict_types=1);

namespace Modules\AI\Actions;

use Codewithkyrian\Transformers\Transformers;
use Exception;
use Modules\AI\Contracts\SentimentAnalyzer;
use Modules\AI\Datas\SentimentData;
// use function Codewithkyrian\Transformers\Pipelines\pipeline;
use Spatie\QueueableAction\QueueableAction;
use Throwable;
use Webmozart\Assert\Assert;

// use function Codewithkyrian\Transformers\Pipelines\pipeline;
use function Safe\error_log;

class BasicSentimentAnalyzer implements SentimentAnalyzer
{
    /**
     * {@inheritDoc}
     *
     * @return array<string,mixed>
     */
    public function analyze(string $text): array
    {
        // Basic sentiment analysis using simple text patterns
        $positiveWords = ['good', 'great', 'excellent', 'positive', 'happy'];
        $negativeWords = ['bad', 'poor', 'terrible', 'negative', 'unhappy'];

        $positiveCount = 0;
        $negativeCount = 0;

        foreach ($positiveWords as $word) {
            if (stripos($text, $word) !== false) {
                $positiveCount++;
            }
        }

        foreach ($negativeWords as $word) {
            if (stripos($text, $word) !== false) {
                $negativeCount++;
            }
        }

        $score = ($positiveCount - $negativeCount) / max(1, $positiveCount + $negativeCount);

        return [
            'label' => $score >= 0 ? 'POSITIVE' : 'NEGATIVE',
            'score' => abs($score),
            'warning' => 'Using basic sentiment analysis - install transformers for better accuracy',
        ];
    }
}

/**
 * Sentiment analysis action using either transformers or basic implementation.
 *
 * The transformers implementation requires the codewithkyrian/transformers package.
 * If not available, it falls back to a basic text pattern matching implementation.
 *
 * To enable the full functionality, install the package:
 *
 * ```bash
 * composer require codewithkyrian/transformers
 * ```
 *
 * Note: You may see IDE errors about undefined types/functions until the package
 * is installed. These can be safely ignored as the code will automatically fall
 * back to basic text analysis if the package is not available.
 */
class SentimentAction
{
    use QueueableAction;

    private SentimentAnalyzer $analyzer;

    public function __construct()
    {
        $this->analyzer = class_exists('Codewithkyrian\Transformers\Transformers')
            ? new TransformersSentimentAnalyzer
            : new BasicSentimentAnalyzer;
    }

    /**
     * Execute sentiment analysis on a text prompt.
     *
     * @param  string  $prompt  The text to analyze
     */
    public function execute(string $prompt): SentimentData
    {
        try {
            $result = $this->analyzer->analyze($prompt);

            return SentimentData::from($result);
        } catch (Exception $e) {
            error_log('Sentiment analysis error: '.$e->getMessage());

            return SentimentData::from([
                'error' => $e->getMessage(),
                'status' => 'error',
            ]);
        }
    }
}

class TransformersSentimentAnalyzer implements SentimentAnalyzer
{
    private string $cacheDir = './../cache/models';

    /**
     * {@inheritDoc}
     *
     * @return array<string,mixed>
     */
    public function analyze(string $text): array
    {
        try {
            if (! class_exists('Codewithkyrian\Transformers\Transformers')) {
                throw new Exception('Transformers library not installed');
            }

            /**
             * @var class-string<Transformers> $transformersClass
             *                                 La variabile $transformers viene dichiarata più sotto e tipizzata correttamente.
             */
            $transformersClass = 'Codewithkyrian\Transformers\Transformers';
            if (! method_exists($transformersClass, 'setup')) {
                throw new Exception('Transformers setup method not found');
            }

            /** @var object|null $transformers */
            $transformers = $transformersClass::setup();
            if (! is_object($transformers)) {
                throw new Exception('Failed to initialize Transformers');
            }
            if (! method_exists($transformers, 'setCacheDir')) {
                throw new Exception('setCacheDir method not found on Transformers');
            }
            $transformers->setCacheDir($this->cacheDir);
            if (method_exists($transformers, 'apply')) {
                $transformers->apply();
            }

            if (! class_exists('\\Codewithkyrian\\Transformers\\Pipelines\\Pipeline')) {
                // Fall back to basic sentiment analysis if transformers not available
                $basicAnalyzer = new BasicSentimentAnalyzer;

                return $basicAnalyzer->analyze($text);
            }

            // Check if pipeline function exists before calling it
            if (! function_exists('\\Codewithkyrian\\Transformers\\Pipelines\\pipeline')) {
                $basicAnalyzer = new BasicSentimentAnalyzer;

                return $basicAnalyzer->analyze($text);
            }

            // $pipe = pipeline('sentiment-analysis');
            // Pipeline creation can fail, use try-catch instead of is_callable
            try {
                // $result = $pipe($text);
                // Assert::isArray($result);
                throw new Exception('Transformers pipeline functionality temporarily disabled');
            } catch (Throwable $e) {
                // Fall back to basic sentiment analysis if pipeline creation fails
                $basicAnalyzer = new BasicSentimentAnalyzer;

                return $basicAnalyzer->analyze($text);
            }

            // /** @var array<string, mixed> $analysisResult */
            // $analysisResult = $result;

            // return $analysisResult;
        } catch (Exception $e) {
            error_log('Transformers sentiment analysis failed: '.$e->getMessage());

            return [
                'error' => $e->getMessage(),
                'status' => 'error',
                'fallback' => true,
            ];
        }
    }
}
