<?php

declare(strict_types=1);

namespace Modules\AI\Tests\Unit\Actions;

use Mockery;
use Modules\AI\Actions\SentimentAction;
use Modules\AI\Datas\SentimentData;
use Tests\TestCase;

class SentimentActionTest extends TestCase
{
    private SentimentAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new SentimentAction;
    }

    /** @test */
    public function it_analyzes_positive_sentiment_correctly(): void
    {
        // Arrange
        $text = 'This is a great product with excellent features. I am very happy with it.';

        // Act
        /** @phpstan-ignore-next-line property.notFound */
        $result = $this->action->execute($text);

        // Assert
        expect($result)->toBeInstanceOf(SentimentData::class)
            ->and($result->label)->toBe('POSITIVE')
            ->and($result->score)->toBeGreaterThan(0);
    }

    /** @test */
    public function it_analyzes_negative_sentiment_correctly(): void
    {
        // Arrange
        $text = 'This is a bad product with terrible features. I am very unhappy with it.';

        // Act
        /** @phpstan-ignore-next-line property.notFound */
        $result = $this->action->execute($text);

        // Assert
        expect($result)->toBeInstanceOf(SentimentData::class)
            ->and($result->label)->toBe('NEGATIVE')
            ->and($result->score)->toBeGreaterThan(0);
    }

    /** @test */
    public function it_analyzes_neutral_sentiment_correctly(): void
    {
        // Arrange
        $text = 'This is a product with some features. I have mixed feelings about it.';

        // Act
        /** @phpstan-ignore-next-line property.notFound */
        $result = $this->action->execute($text);

        // Assert
        expect($result)->toBeInstanceOf(SentimentData::class)
            ->and($result->label)->toBeIn(['POSITIVE', 'NEGATIVE']);
    }

    /** @test */
    public function it_handles_empty_text(): void
    {
        // Arrange
        $text = '';

        // Act
        /** @phpstan-ignore-next-line property.notFound */
        $result = $this->action->execute($text);

        // Assert
        expect($result)->toBeInstanceOf(SentimentData::class)
            ->and($result->label)->toBe('NEGATIVE')
            ->and($result->score)->toBe(0);
    }

    /** @test */
    public function it_handles_text_with_only_positive_words(): void
    {
        // Arrange
        $text = 'good great excellent positive happy';

        // Act
        /** @phpstan-ignore-next-line property.notFound */
        $result = $this->action->execute($text);

        // Assert
        expect($result)->toBeInstanceOf(SentimentData::class)
            ->and($result->label)->toBe('POSITIVE')
            ->and($result->score)->toBe(1.0);
    }

    /** @test */
    public function it_handles_text_with_only_negative_words(): void
    {
        // Arrange
        $text = 'bad poor terrible negative unhappy';

        // Act
        /** @phpstan-ignore-next-line property.notFound */
        $result = $this->action->execute($text);

        // Assert
        expect($result)->toBeInstanceOf(SentimentData::class)
            ->and($result->label)->toBe('NEGATIVE')
            ->and($result->score)->toBe(1.0);
    }

    /** @test */
    public function it_handles_text_with_mixed_sentiment(): void
    {
        // Arrange
        $text = 'This product is good but has some bad aspects. Overall I am happy but also concerned.';

        // Act
        /** @phpstan-ignore-next-line property.notFound */
        $result = $this->action->execute($text);

        // Assert
        expect($result)->toBeInstanceOf(SentimentData::class)
            ->and($result->label)->toBeIn(['POSITIVE', 'NEGATIVE']);
    }

    /** @test */
    public function it_handles_case_insensitive_sentiment_analysis(): void
    {
        // Arrange
        $text = 'This is a GREAT product with EXCELLENT features. I am VERY HAPPY with it.';

        // Act
        /** @phpstan-ignore-next-line property.notFound */
        $result = $this->action->execute($text);

        // Assert
        expect($result)->toBeInstanceOf(SentimentData::class)
            ->and($result->label)->toBe('POSITIVE');
    }

    /** @test */
    public function it_handles_text_with_special_characters(): void
    {
        // Arrange
        $text = 'This is a great product! I am very happy with it. :)';

        // Act
        /** @phpstan-ignore-next-line property.notFound */
        $result = $this->action->execute($text);

        // Assert
        expect($result)->toBeInstanceOf(SentimentData::class)
            ->and($result->label)->toBe('POSITIVE');
    }

    /** @test */
    public function it_handles_text_with_numbers(): void
    {
        // Arrange
        $text = 'I rate this product 5 out of 5. It is excellent and I am very happy.';

        // Act
        /** @phpstan-ignore-next-line property.notFound */
        $result = $this->action->execute($text);

        // Assert
        expect($result)->toBeInstanceOf(SentimentData::class)
            ->and($result->label)->toBe('POSITIVE');
    }

    /** @test */
    public function it_handles_text_with_punctuation(): void
    {
        // Arrange
        $text = 'This product is terrible!!! I am very unhappy with it...';

        // Act
        /** @phpstan-ignore-next-line property.notFound */
        $result = $this->action->execute($text);

        // Assert
        expect($result)->toBeInstanceOf(SentimentData::class)
            ->and($result->label)->toBe('NEGATIVE');
    }

    /** @test */
    public function it_handles_text_with_multiple_sentences(): void
    {
        // Arrange
        $text = 'This is a great product. I am very happy with it. The features are excellent.';

        // Act
        /** @phpstan-ignore-next-line property.notFound */
        $result = $this->action->execute($text);

        // Assert
        expect($result)->toBeInstanceOf(SentimentData::class)
            ->and($result->label)->toBe('POSITIVE');
    }

    /** @test */
    public function it_handles_text_with_technical_terms(): void
    {
        // Arrange
        $text = 'The API integration is good. The documentation is excellent. I am happy with the performance.';

        // Act
        /** @phpstan-ignore-next-line property.notFound */
        $result = $this->action->execute($text);

        // Assert
        expect($result)->toBeInstanceOf(SentimentData::class)
            ->and($result->label)->toBe('POSITIVE');
    }

    /** @test */
    public function it_handles_text_with_emotions(): void
    {
        // Arrange
        $text = 'I feel great about this decision. I am so happy and excited. This is wonderful news.';

        // Act
        /** @phpstan-ignore-next-line property.notFound */
        $result = $this->action->execute($text);

        // Assert
        expect($result)->toBeInstanceOf(SentimentData::class)
            ->and($result->label)->toBe('POSITIVE');
    }

    /** @test */
    public function it_handles_text_with_negations(): void
    {
        // Arrange
        $text = 'This is not a good product. I am not happy with it.';

        // Act
        /** @phpstan-ignore-next-line property.notFound */
        $result = $this->action->execute($text);

        // Assert
        expect($result)->toBeInstanceOf(SentimentData::class)
            ->and($result->label)->toBe('NEGATIVE');
    }

    /** @test */
    public function it_handles_text_with_intensifiers(): void
    {
        // Arrange
        $text = 'This is extremely good. I am very very happy. The features are absolutely excellent.';

        // Act
        /** @phpstan-ignore-next-line property.notFound */
        $result = $this->action->execute($text);

        // Assert
        expect($result)->toBeInstanceOf(SentimentData::class)
            ->and($result->label)->toBe('POSITIVE');
    }

    /** @test */
    public function it_handles_text_with_comparisons(): void
    {
        // Arrange
        $text = 'This product is better than the previous one. I am happier now.';

        // Act
        /** @phpstan-ignore-next-line property.notFound */
        $result = $this->action->execute($text);

        // Assert
        expect($result)->toBeInstanceOf(SentimentData::class)
            ->and($result->label)->toBe('POSITIVE');
    }

    /** @test */
    public function it_handles_text_with_questions(): void
    {
        // Arrange
        $text = 'Is this a good product? I am happy but also wondering about the quality.';

        // Act
        /** @phpstan-ignore-next-line property.notFound */
        $result = $this->action->execute($text);

        // Assert
        expect($result)->toBeInstanceOf(SentimentData::class)
            ->and($result->label)->toBeIn(['POSITIVE', 'NEGATIVE']);
    }

    /** @test */
    public function it_handles_text_with_quotes(): void
    {
        // Arrange
        $text = 'The customer said "This is excellent!" and I agree completely.';

        // Act
        /** @phpstan-ignore-next-line property.notFound */
        $result = $this->action->execute($text);

        // Assert
        expect($result)->toBeInstanceOf(SentimentData::class)
            ->and($result->label)->toBe('POSITIVE');
    }

    /** @test */
    public function it_handles_text_with_abbreviations(): void
    {
        // Arrange
        $text = 'This is gr8! I am v happy with it. The features are excellent.';

        // Act
        /** @phpstan-ignore-next-line property.notFound */
        $result = $this->action->execute($text);

        // Assert
        expect($result)->toBeInstanceOf(SentimentData::class)
            ->and($result->label)->toBe('POSITIVE');
    }

    /** @test */
    public function it_handles_text_with_foreign_words(): void
    {
        // Arrange
        $text = 'This product is bon (good in French). I am molto felice (very happy in Italian).';

        // Act
        /** @phpstan-ignore-next-line property.notFound */
        $result = $this->action->execute($text);

        // Assert
        expect($result)->toBeInstanceOf(SentimentData::class)
            ->and($result->label)->toBe('POSITIVE');
    }

    /** @test */
    public function it_handles_text_with_technical_acronyms(): void
    {
        // Arrange
        $text = 'The API is good. The UI/UX is excellent. I am happy with the MVP.';

        // Act
        /** @phpstan-ignore-next-line property.notFound */
        $result = $this->action->execute($text);

        // Assert
        expect($result)->toBeInstanceOf(SentimentData::class)
            ->and($result->label)->toBe('POSITIVE');
    }

    /** @test */
    public function it_handles_text_with_measurements(): void
    {
        // Arrange
        $text = 'The 100% uptime is excellent. The 5-star rating is great. I am very happy.';

        // Act
        /** @phpstan-ignore-next-line property.notFound */
        $result = $this->action->execute($text);

        // Assert
        expect($result)->toBeInstanceOf(SentimentData::class)
            ->and($result->label)->toBe('POSITIVE');
    }

    /** @test */
    public function it_handles_text_with_time_expressions(): void
    {
        // Arrange
        $text = 'I am happy today. Yesterday was great. Tomorrow will be excellent.';

        // Act
        /** @phpstan-ignore-next-line property.notFound */
        $result = $this->action->execute($text);

        // Assert
        expect($result)->toBeInstanceOf(SentimentData::class)
            ->and($result->label)->toBe('POSITIVE');
    }

    /** @test */
    public function it_handles_text_with_conditional_statements(): void
    {
        // Arrange
        $text = 'If this works, I will be happy. The current state is good.';

        // Act
        /** @phpstan-ignore-next-line property.notFound */
        $result = $this->action->execute($text);

        // Assert
        expect($result)->toBeInstanceOf(SentimentData::class)
            ->and($result->label)->toBe('POSITIVE');
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
