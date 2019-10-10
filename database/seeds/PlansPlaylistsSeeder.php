<?php

use App\Models\Plan\Plan;
use App\Models\Plan\PlanDay;
use App\Models\Plan\UserPlan;
use App\Models\Playlist\Playlist;
use App\Models\Playlist\PlaylistItems;
use Illuminate\Database\Seeder;
use Faker\Generator as Faker;

class PlansPlaylistsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @param Faker $faker
     *
     * @return void
     */
    public function run(Faker $faker)
    {
        $users = \App\Models\User\User::all();

        $filesets = \DB::connection('dbp')
            ->table('bible_filesets')
            ->where('set_type_code', 'audio_drama')
            ->leftJoin(config('database.connections.dbp.database') . '.bible_files', function ($q) {
                $q->on('bible_filesets.hash_id', 'bible_files.hash_id');
            })
            ->select(['bible_filesets.id', 'bible_filesets.hash_id'])->get();

        Plan::create([
            'user_id'               => 1,
            'name'                  => 'Featured Plan',
            'featured'              => true,
            'suggested_start_date'  => $faker->date
        ]);

        Playlist::create([
            'user_id'               => 1,
            'name'                  => 'Featured Playlist',
            'featured'              => true,
            'external_content'      => $faker->url,
        ]);


        foreach ($users as $user) {
            if (random_int(0, 1)) {
                $plan_count = random_int(1, 5);
                while ($plan_count > 0) {
                    $plan = Plan::create([
                        'user_id'               => $user->id,
                        'name'                  => $faker->name,
                        'featured'              => false,
                        'suggested_start_date'  => $faker->date
                    ]);

                    $days = random_int(0, 20);
                    for ($i = 0; $i < intval($days); $i++) {
                        $playlist = Playlist::create([
                            'user_id'               => $user->id,
                        ]);

                        $fileset = $filesets->random();

                        $bible_files = \DB::connection('dbp')->table('bible_files')->where('hash_id', $fileset->hash_id)->get();

                        if (sizeof($bible_files)) {
                            $bible_file = $bible_files->random();
                            PlaylistItems::create([
                                'playlist_id'       => $playlist->id,
                                'fileset_id'        => $fileset->id,
                                'book_id'           => $bible_file->book_id,
                                'chapter_start'     => $bible_file->chapter_start,
                                'chapter_end'       => $bible_file->chapter_end,
                                'verse_start'       => $bible_file->verse_start,
                                'verse_end'         => $bible_file->verse_end
                            ]);
                        }

                        PlanDay::create([
                            'plan_id'               => $plan->id,
                            'playlist_id'           => $playlist->id,
                        ]);
                    }

                    UserPlan::create([
                        'user_id'               => $user->id,
                        'plan_id'               => $plan->id
                    ]);

                    $plan_count--;
                }
            }
            if (random_int(0, 1)) {
                $playlist = Playlist::create([
                    'user_id'               => $user->id,
                    'name'                  => $faker->name,
                    'featured'              => true,
                    'external_content'      => $faker->url,
                ]);
                $fileset = $filesets->random();

                $bible_files = \DB::connection('dbp')->table('bible_files')->where('hash_id', $fileset->hash_id)->get();

                if (sizeof($bible_files)) {
                    $bible_file = $bible_files->random();
                    PlaylistItems::create([
                        'playlist_id'       => $playlist->id,
                        'fileset_id'        => $fileset->id,
                        'book_id'           => $bible_file->book_id,
                        'chapter_start'     => $bible_file->chapter_start,
                        'chapter_end'       => $bible_file->chapter_end,
                        'verse_start'       => $bible_file->verse_start,
                        'verse_end'         => $bible_file->verse_end
                    ]);
                }
            }
        }
    }
}
