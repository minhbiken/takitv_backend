#!/bin/bash
echo "post_id = $1 "
echo "tmdb_id = $2 "
echo "type = $3 "

if [[ $3 == 'movie' ]]
then
  echo "Run movie.js $1 $2"
  cd /home/vuejs/backend && node ./../crawler_person/movie.js $1 $2
else
  echo "Run tv_show.js $1 $2"
  cd /home/vuejs/backend && node ./../crawler_person/tv_show.js $1 $2
fi

cd /home/vuejs/backend && php artisan person:insert data.json $3