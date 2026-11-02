<?php

namespace Database\Seeders;

use App\Enums\Difficulty;
use App\Models\Category;
use App\Models\Quiz;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Demo exams for every category and difficulty.
 *
 * Question banks live in database/seeders/data/questions/{category-slug}.json:
 *   { "easy": [ ["Question?", ["correct", "wrong", "wrong", "wrong"]], ... ], "standard": [...], ... }
 * The first option is always the correct one; options are shuffled when seeding.
 *
 * An optional "meta" block lets a bank create its own category and use localized titles:
 *   "meta": { "category": "Islamic", "title": "ইসলামিক কুইজ — {level} পরীক্ষা {number}",
 *             "description": "{level} স্তরের ... {count}টি প্রশ্ন", "bangla_numbers": true }
 * {level} becomes the Bangla level name when bangla_numbers is true, otherwise the English one.
 *
 * Each exam draws QUESTIONS_PER_EXAM questions from its level's bank, so exams of the
 * same level overlap but are never identical. Existing exams (same title) are skipped,
 * so the seeder is safe to run again.
 *
 * Run: php artisan db:seed --class=DemoQuizSeeder
 */
class DemoQuizSeeder extends Seeder {
    private const EXAMS_PER_LEVEL   = 5;
    private const QUESTIONS_PER_EXAM = 25;

    public function run(): void {
        $categories = Category::all()->keyBy( 'slug' );

        foreach ( glob( database_path( 'seeders/data/questions/*.json' ) ) as $file ) {
            $slug = basename( $file, '.json' );
            $bank = json_decode( file_get_contents( $file ), true, flags: JSON_THROW_ON_ERROR );
            $meta = $bank['meta'] ?? [];

            $category = $categories->get( $slug )
                ?? ( isset( $meta['category'] )
                    ? Category::create( ['name' => $meta['category'], 'slug' => $slug, 'status' => true] )
                    : null );

            if ( !$category ) {
                $this->command?->warn( "Skipping {$slug}.json: no category with slug '{$slug}'." );
                continue;
            }

            foreach ( Difficulty::cases() as $level ) {
                $questions = $bank[$level->value] ?? [];

                if ( count( $questions ) < self::QUESTIONS_PER_EXAM ) {
                    $this->command?->warn( "Skipping {$category->name} / {$level->label()}: fewer than " . self::QUESTIONS_PER_EXAM . ' questions.' );
                    continue;
                }

                for ( $number = 1; $number <= self::EXAMS_PER_LEVEL; $number++ ) {
                    $this->createExam( $category, $level, $number, $questions, $meta );
                }
            }

            $this->command?->info( "Seeded {$category->name}" );
        }
    }

    private function createExam( Category $category, Difficulty $level, int $number, array $bank, array $meta ): void {
        $bangla       = (bool) ( $meta['bangla_numbers'] ?? false );
        $placeholders = [
            '{level}'  => $bangla ? $level->banglaLabel() : $level->label(),
            '{number}' => $bangla ? $this->banglaDigits( $number ) : (string) $number,
            '{count}'  => $bangla ? $this->banglaDigits( self::QUESTIONS_PER_EXAM ) : (string) self::QUESTIONS_PER_EXAM,
        ];

        $title = isset( $meta['title'] )
            ? strtr( $meta['title'], $placeholders )
            : "{$category->name} {$level->label()} Exam {$number}";

        $description = isset( $meta['description'] )
            ? strtr( $meta['description'], $placeholders )
            : "{$level->label()} level {$category->name} exam with " . self::QUESTIONS_PER_EXAM . ' questions.';

        if ( Quiz::where( 'title', $title )->exists() ) {
            return;
        }

        // A fixed seed per exam keeps the question picks and option order stable between runs
        mt_srand( crc32( $title ) );
        $picked = $this->shuffled( $bank );
        $picked = array_slice( $picked, 0, self::QUESTIONS_PER_EXAM );

        DB::transaction( function () use ( $category, $level, $number, $title, $description, $picked ) {
            $quiz = Quiz::create( [
                'category_id' => $category->id,
                'title'       => $title,
                // Built from ASCII parts so localized (e.g. Bangla) titles still get a readable slug
                'slug'        => Str::slug( "{$category->slug} {$level->value} exam {$number}" ),
                'description' => $description,
                'difficulty'  => $level,
            ] );

            // One insert for all questions, then one insert for all of their answers
            $now = now();
            $quiz->questions()->insert( array_map( fn( $item ) => [
                'quiz_id'    => $quiz->id,
                'question'   => $item[0],
                'created_at' => $now,
                'updated_at' => $now,
            ], $picked ) );

            $questionIds = $quiz->questions()->orderBy( 'id' )->pluck( 'id' );

            $answers = [];
            foreach ( $picked as $index => [, $options] ) {
                $correct = $options[0];

                foreach ( $this->shuffled( $options ) as $option ) {
                    $answers[] = [
                        'question_id' => $questionIds[$index],
                        'answer'      => $option,
                        'is_correct'  => $option === $correct,
                        'created_at'  => $now,
                        'updated_at'  => $now,
                    ];
                }
            }

            DB::table( 'answers' )->insert( $answers );
        } );
    }

    private function banglaDigits( int $number ): string {
        return strtr( (string) $number, ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'] );
    }

    /**
     * Shuffle using mt_rand so results follow the seed set in createExam().
     */
    private function shuffled( array $items ): array {
        for ( $i = count( $items ) - 1; $i > 0; $i-- ) {
            $j = mt_rand( 0, $i );
            [$items[$i], $items[$j]] = [$items[$j], $items[$i]];
        }

        return $items;
    }
}
