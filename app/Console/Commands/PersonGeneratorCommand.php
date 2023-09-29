<?php
namespace App\Console\Commands;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Post;
use App\Models\PostMeta;
class PersonGeneratorCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'person:insert {file : put your file into /home/vuejs/backend/storage/app and run: php artisan person:insert data.json}
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Insert Person';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
            DB::beginTransaction();
            $file = $this->argument('file');
            $personList = json_decode(Storage::disk('local')->get($file), true);
            $personListRollback = [];
            $personMetaListRollback = [];
            foreach( $personList as $person) {
                $person = json_decode($person, true);
                $person = $person[0];

                $movieId = $person['movie_id'];
                $tmdbId = $person['tmdb_id'];
                $guid = $person['link'];
                $image = $person['image'];
                $title = $person['name'];
                $name = str_replace(' ', '-',(strtolower($person['name'])));

                if (Post::where([ 'post_title' => $title, 'post_status' => 'publish'])->exists()) {
                    $person = Post::select('ID')->where(['post_title'=> $title, 'post_status' => 'publish'])->first();
                    $idNewPerson = $person->ID;
                    $dataMovie =  PostMeta::select('meta_id','meta_value')->where(['post_id' => $idNewPerson, 'meta_key' => '_movie_cast'])->first();
                    $movies = unserialize($dataMovie->meta_value);
                    
                    //check exist and update movie of cast
                    if( !in_array($movieId, $movies) ) {
                        array_push($movies, $movieId);
                        $metaPost = PostMeta::find($dataMovie->meta_id);
                        $metaPost->meta_value = serialize($movies);
                        $metaPost->save();
                    }
                } else {
                    $newPerson = Post::create(
                        [
                            'post_title' => $title,
                            'post_name' => $name,
                            'post_content' => $title, 
                            'post_status' => 'publish',
                            'post_author' => 1,
                            'comment_status' => 'closed',
                            'ping_status' => 'closed',
                            'guid' => $guid, 
                            'post_type' => 'person', 
                            'post_excerpt' => '', 
                            'to_ping' => '', 
                            'pinged' => '',
                            'post_content_filtered' => '',
                            'post_date' => now(),
                            'post_date_gmt' => now(),
                            'post_modified' => now(),
                            'post_modified_gmt' => now()
                        ]
                    );

                    $idNewPerson = $newPerson->ID;

                    //insert tmdb id
                    $idPostMeta_tmdb_id = PostMeta::insertGetId([
                        'post_id' => $idNewPerson, 
                        'meta_key' => '_tmdb_id',
                        'meta_value' => $tmdbId, 
                    ]);
                    array_push($personMetaListRollback, $idPostMeta_tmdb_id);

                    //insert image custom
                    $idPostMeta_person_image_custom = PostMeta::insertGetId([
                        'post_id' => $idNewPerson, 
                        'meta_key' => '_person_image_custom',
                        'meta_value' => $image,
                    ]);
                    array_push($personMetaListRollback, $idPostMeta_person_image_custom);

                    //insert cast movie
                    $idPostMeta_movie_cast = PostMeta::insertGetId([
                        'post_id' => $idNewPerson, 
                        'meta_key' => '_movie_cast',
                        'meta_value' =>  serialize(array($movieId)) 
                    ]);
                    array_push($personMetaListRollback, $idPostMeta_movie_cast);

                    //update movie cast
                    $dataMovieCast =  PostMeta::select('meta_id','meta_value')->where(['post_id' => $movieId, 'meta_key' => '_cast'])->first();
                    if( $dataMovieCast->meta_value == '' ) {
                        $movieCasts = [];
                        $newCastMovie = [
                            'id' => $idNewPerson,
                            'character' => '',
                            'position' => 0,
                        ];
                    } else {
                        $movieCasts = unserialize($dataMovieCast->meta_value);
                        //check exist and update movie of cast
                        foreach($movieCasts as $movieCast ) {
                            if( $movieCast['id'] != $idNewPerson ) {
                                $newCastMovie = [
                                    'id' => $idNewPerson,
                                    'character' => '',
                                    'position' => end($movieCasts)['position']++,
                                ];
                            }
                        }    
                    }
                    array_push($movieCasts, $newCastMovie);
                    $metaPostMovie = PostMeta::find($dataMovieCast->meta_id);
                    $metaPostMovie->meta_value = serialize($movieCasts);
                    $metaPostMovie->save();
                }
                array_push($personListRollback, $idNewPerson);
            }
            Storage::disk('local')->put('rollback_person.json', json_encode($personListRollback));
            Storage::disk('local')->put('rollback_person_meta.json', json_encode($personMetaListRollback));         
            
            DB::commit();
           
            //send output to the console
            $this->info('Success!');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error($e->getMessage());
        }
    }
}
