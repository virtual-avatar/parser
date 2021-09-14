<?php


namespace App\Service\Parser;

use App\Entity\Product;
use App\Service\Monolog\Log;
use App\Service\Parser\InterfaceParser\ParserWorker;
use Symfony\Component\Panther\Client;

class FoxtrotParser implements ParserWorker
{
    private array $url;

    /**
     * Foxtrot constructor.
     * @param array $url
     */
    public function __construct(array $url)
    {
        $this->url = $url;
    }

    public function run():void
    {
        foreach ($this->url as $key => $value) {
            if($this->parsing($value) === false) {
                $log = Log::getInstance();
                $log->error("не удалось распарсить страницу " . $value);
            }
        }
    }

    private function parsing($url): bool
    {
        $schema = parse_url($url,PHP_URL_SCHEME);
        $host = parse_url($url,PHP_URL_HOST);
        //для виндовс возможно потребуется указать полный путь до каталога с chromedriver.exe
        //например 'E:\OSPanel\domains\parser.loc\vendor\bin\drivers\chromedriver.exe'
        $client = Client::createChromeClient();
        try {
            $crawler = $client->request('GET', $url);
            //ждем загрузки списка товаров на странице
            $crawler = $client->waitFor('.listing__body');
            $maxPageInPagination = $crawler->filter('.listing__pagination')->attr('data-pages-count');
            $i = 0;
            do{
                $i++;
                //массив ссылок на товар на отдельной странице
                $links = $crawler->filter('div.card__body > a.card__title')->each(function ($node, $i) {
                    return $node->attr('href');
                });
                foreach($links as $link) {
                    //перебираем найденные товары
                    $client->restart();
                    $crawler = $client->request('GET', $schema . '://' . $host . $link);
                    $crawler = $client->waitFor('#product-page-title');
                    //базовые параметры твара
                    $goods = [
                        'наименование' => $crawler->filter('#product-page-title')->text(),
                        'цена' => $crawler->filter('.card-price')->text(),
                    ];
                    $crawler = $client->clickLink('ВСЕ ХАРАКТЕРИСТИКИ');
                    $crawler = $client->waitFor('#product-specs-popup');
                    $table = $crawler->filter('table.popup-table_v2')->filter('tbody')->filter('tr')->each(function ($tr, $i) {
                        return $tr->filter('td')->each(function ($td, $i) {
                            return trim($td->text());
                        });
                    });
                    foreach ($table as $item) {
                        $tableItem[$item[0]] = $item[1];
                    }
                    //собираем в в общий массив данных по товару
                    if(!empty($tableItem)) {
                        $goodsArray = array_merge($goods,$tableItem);
                    } else {
                        $goodsArray = $goods;
                    }
                    $productArray = array_filter($goodsArray, function($element) {
                        return !empty($element);
                    });
                    //сохраняем в БД
                    $product = new Product($productArray);
                    if($product->save() === false) {
                        $log = Log::getInstance();
                        $log->error("Не удалось сохранить данные в БД. \n Исходные данные: \n ". print_r($goodsArray,true));
                    }
                    //$array[] = $goodsArray;
                    //переходим на назад страницу к оставшимся товарам
                    $crawler = $client->request('GET', $url);
                    $crawler = $client->waitFor('.listing__body');
                }
                //переходим на следующую страницу по пагинатору
                $crawler = $client->request('GET', $url . '?page=' . $i);
                $crawler = $client->waitFor('.listing__body');
            }while($i <= $maxPageInPagination);
        } catch (\Exception $e) {
            Log::error($e);
            return false;
        }
        return true;
    }

}