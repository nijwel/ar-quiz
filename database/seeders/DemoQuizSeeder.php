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
            $slug     = basename( $file, '.json' );
            $category = $categories->get( $slug );

            if ( !$category ) {
                $this->command?->warn( "Skipping {$slug}.json: no category with slug '{$slug}'." );
                continue;
            }

            $bank = json_decode( file_get_contents( $file ), true, flags: JSON_THROW_ON_ERROR );

            foreach ( Difficulty::cases() as $level ) {
                $questions = $bank[$level->value] ?? [];

                if ( count( $questions ) < self::QUESTIONS_PER_EXAM ) {
                    $this->command?->warn( "Skipping {$category->name} / {$level->label()}: fewer than " . self::QUESTIONS_PER_EXAM . ' questions.' );
                    continue;
                }

                for ( $number = 1; $number <= self::EXAMS_PER_LEVEL; $number++ ) {
                    $this->createExam( $category, $level, $number, $questions );
                }
            }

            $this->command?->info( "Seeded {$category->name}" );
        }
    }

    private function createExam( Category $category, Difficulty $level, int $number, array $bank ): void {
        $title = "{$category->name} {$level->label()} Exam {$number}";

        if ( Quiz::where( 'title', $title )->exists() ) {
            return;
        }

        // A fixed seed per exam keeps the question picks and option order stable between runs
        mt_srand( crc32( $title ) );
        $picked = $this->shuffled( $bank );
        $picked = array_slice( $picked, 0, self::QUESTIONS_PER_EXAM );

        DB::transaction( function () use ( $category, $level, $title, $picked ) {
            $quiz = Quiz::create( [
                'category_id' => $category->id,
                'title'       => $title,
                'slug'        => Str::slug( $title ),
                'description' => "{$level->label()} level {$category->name} exam with " . count( $picked ) . ' questions.',
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
