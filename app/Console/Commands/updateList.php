<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\DomCrawler\Crawler;
use Psr\Http\Message\ResponseInterface;

use Spatie\Emoji\Emoji;

class updateList extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send Email To Test Addresses';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $client = new GuzzleClient();
        $resp = [];
        for ($page_num = 1; $page_num < 40; $page_num++) {
//            https://store.playstation.com/en-us/grid/STORE-MSF77008-ALLGAMES/1?direction=desc&gameType=ps4_full_games&price=2000-3999%2C4000-5999&sort=release_date
            $url = "https://store.playstation.com/en-us/grid/STORE-MSF77008-ALLGAMES/$page_num?direction=desc&gameType=ps4_full_games&price=2000-3999%2C4000-5999&sort=release_date";
            $config = [
                'referer' => true,
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8',
                    'Accept-Encoding' => 'gzip, deflate, br',
                ],
            ];
            $promise = $client->requestAsync('GET', $url, $config)->then(function (ResponseInterface $response) {
                $content = $response->getBody()->getContents();
                return $content;
            });
            $promise = $promise->wait();
            $xPath_game_list = '//html/body/div[3]/div/div/div[2]/div/div[2]/div[2]';
            $crawler = new Crawler($promise);

            $gamesList = $crawler->filterXPath('//div[contains(@class,"grid-cell--game")]');

            $temp = $gamesList->each(
                function (Crawler $node, $i) {
                    $titles = $node->filterXPath('//div[@class="grid-cell__title "]/span')->text();
                    try {
                        $prices = $node->filterXPath('//h3[@class="price-display__price"]')->text();
                    } catch (\Exception $e) {
                        $prices = 'Not Available';
                    }
                    $images = $node->filterXPath('//img[contains(@class,"product-image__img product-image__img--product product-image__img-main")]')->attr('src');
                    $links = "https://store.playstation.com" . $node->filterXPath('//a[contains(@class,"internal-app-link ember-view")]')->attr('href');
                    return [$titles, $prices, $images, $links];

                });
            array_push($resp, $temp);
            sleep(3);
        }
        Cache::forever('resp',$resp);
        $title_list_arr = DB::table('ps4_game')->get()->pluck('title')->toArray();

        for($t=0;$t<count($resp);$t++){
            for($s=0;$s<count($resp[$t]);$s++){
                if(!in_array($resp[$t][$s][0],$title_list_arr)){
                    $qery = DB::table('ps4_game')->insert([
                        'title'=>$resp[$t][$s][0],
                        'price'=>$resp[$t][$s][1],
                        'avatar'=>$resp[$t][$s][2],
                        'link'=>$resp[$t][$s][3],
                        'created_at'=>Carbon::now()
                    ]);
                }
            }
        }
    }
}
