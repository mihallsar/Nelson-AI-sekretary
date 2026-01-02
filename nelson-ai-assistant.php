<?php
/*
Plugin Name: Nelson AI Assistant
Description: Анимированный русскоязычный голосовой ассистент для WordPress с поддержкой сценариев, fallback-ответами и STOP-кнопкой.
Version: 1.2.0
Text Domain: nelson-ai
*/

if (!defined('ABSPATH')) exit;
define('NELSON_VERSION', '1.2.0');
define('NELSON_DIR', plugin_dir_path(__FILE__));
define('NELSON_URL', plugin_dir_url(__FILE__));

class Nelson_AI_Assistant {
    public static function get_instance() {
        static $instance = null;
        return $instance ?: $instance = new self;
    }
    public function __construct() {
        add_action('wp_enqueue_scripts', [$this,'enqueue_assets']);
        add_action('wp_ajax_nelson_process', [$this,'process_command']);
        add_action('wp_ajax_nopriv_nelson_process', [$this,'process_command']);
        add_action('wp_ajax_nelson_hide_welcome', [$this,'hide_welcome']);
        add_action('wp_ajax_nopriv_nelson_hide_welcome', [$this,'hide_welcome']);
        add_action('wp_footer', [$this,'render_interface']);
    }
    public function enqueue_assets() {
        wp_enqueue_style('nelson-ai-style', NELSON_URL . 'assets/css/style.css', [],NELSON_VERSION);
        wp_enqueue_script('nelson-js', NELSON_URL . 'assets/js/nelson.js', ['jquery'],NELSON_VERSION,true);
        wp_localize_script('nelson-js', 'nelsonData', [
            'ajaxUrl'=>admin_url('admin-ajax.php'),
            'nonce'=>wp_create_nonce('nelson_nonce'),
            'name'=>get_option('nelson_name','Нельсон')
        ]);
    }
    public function process_command() {
        check_ajax_referer('nelson_nonce', 'nonce');
        $command = isset($_POST['command']) ? mb_strtolower(trim($_POST['command'])) : '';
        $result = $this->parse_command($command);
        if ($result) {
            wp_send_json_success($result);
        } else {
            wp_send_json_success([
                'type'=>'message',
                'message'=>'Извините, я еще только учусь и многое не умею, в том числе и этого.'
            ]);
        }
    }
    private function parse_command($cmd) {
        // Дата-фильтры
        if (preg_match('/сегодня|сегодняшн/', $cmd)) return $this->posts_by_date(0);
        if (preg_match('/вчера/', $cmd)) return $this->posts_by_date(-1);
        if (preg_match('/недел[я|и]|за неделю/', $cmd)) return $this->posts_by_days(7);
        if (preg_match('/месяц|за месяц|прошлый месяц|декабрь|ноябрь/', $cmd)) return $this->posts_by_days(30);
        if (preg_match('/два месяца|последние два месяца/', $cmd)) return $this->posts_by_days(60);

        // Последние/главные - топы/дайджесты
        if (preg_match('/последн|свеж|новые|топ|главные|лучшие|дайджест|дай обзор|отчет|брриф|апдейт|обзор/', $cmd)) return $this->posts_latest();

        // Поиск по ключу, тематике, тегам, фразам
        // Включает все твои сложные конструкции
        if (preg_match('/найди|поиск|записи с ссылк|выбери|собери|расскажи|подбери|сделай|сгенерируй|дай подборк|апдейт|закинь|чекни|кинь|оформи|отфильтруй|что есть|объясни|напиши|аналитика|факты|подскажи|найдёшь|обнови|список|кратко|маркерами|тезисы|короткими|в переписке|в чате|перечисли/', $cmd)) {
            $topic = preg_replace('/[^\wа-яё0-9 ]/ui', ' ', $cmd);
            $topic = trim(preg_replace('/(найди|поиск|выбери|сделай|покажи|прочти|прочитай|обзор|расскажи|категории?|новости|робототехни[кч]|ai|интеллект|искусств|фи|машинное обучение|обучени[ея]|дроны|медтех|чате?|стартапы|бизнес|образовани[ея]|медицина|технолог|автотранспорт|креа|клауде|джемени|аттроник|инструменты?|гайд|отчет|дай|основные|апдейт|брриф|отфильтруй|маркерами|тезисы|таблица|объясни|факты|отметь|обзор|аналитика|наука|большее|важнее|новейше[еийяюю]?|всех|прошл|ключе[выеийяюю]?|только|глав[ныеияюю]?|по теме|про|подбери|сортировки?)/ui','',$topic));
            return $this->search_by_keyword($topic ?: $cmd);
        }
        // Фильтр по рубрике/категории ("открой Идей копилка", "новости из Телеграм" и пр)
        $categories = get_categories(['hide_empty'=>false]);
        foreach ($categories as $cat) {
            if (mb_stripos($cmd, mb_strtolower($cat->name)) !== false) {
                return $this->posts_by_category($cat->name);
            }
        }
        return null;
    }
    private function posts_latest() {
        $posts = get_posts(['numberposts'=>5, 'post_status'=>'publish']);
        return $this->render_cards($posts, 'Последние новости');
    }
    private function posts_by_date($offsetDays) {
        $date_query = ($offsetDays==0) ?
            ['after'=>date('Y-m-d 00:00:00')] :
            ['year'=>date('Y'),'month'=>date('m'),'day'=>date('d',strtotime("$offsetDays days"))];
        $posts = get_posts(['date_query'=>[$date_query],'numberposts'=>10]);
        return $this->render_cards($posts, ($offsetDays==0)? 'Новости за сегодня':'Новости за вчера');
    }
    private function posts_by_days($days) {
        $after = date('Y-m-d', strtotime("-$days days"));
        $posts = get_posts(['date_query'=>[['after'=>$after]],'numberposts'=>20]);
        return $this->render_cards($posts, "Новости за $days дней");
    }
    private function posts_by_category($cat_name) {
        $cat = get_category_by_slug(sanitize_title($cat_name));
        if(!$cat) return null;
        $posts = get_posts(['category'=>$cat->term_id,'numberposts'=>10]);
        return $this->render_cards($posts, "Новости рубрики: $cat->name");
    }
    private function search_by_keyword($keyword) {
        if(!$keyword) return null;
        $posts = get_posts(['s'=>$keyword, 'numberposts'=>10]);
        if(!$posts) return null;
        return $this->render_cards($posts, "Результаты поиска: $keyword");
    }
    private function render_cards($posts, $header='Новости') {
        if(!$posts) return ['type'=>'message','message'=>'Извините, я еще только учусь и многое не умею, в том числе и этого.'];
        $items = [];
        foreach($posts as $post) $items[]=[
            'id'=>$post->ID,
            'title'=>$post->post_title,
            'url'=>get_permalink($post),
            'excerpt'=>wp_trim_words(strip_tags($post->post_content), 30),
            'date'=>get_the_date('d.m.Y', $post->ID)
        ];
        return ['type'=>'cards','title'=>$header,'items'=>$items,'count'=>count($items)];
    }
    public function hide_welcome() {
        check_ajax_referer('nelson_nonce', 'nonce');
        if(is_user_logged_in()) update_user_meta(get_current_user_id(),'nelson_hide_welcome','1');
        wp_send_json_success();
    }
    public function render_interface() {
        $show_welcome = !isset($_COOKIE['nelson_hide_welcome']);
        include NELSON_DIR.'includes/interface.php';
    }
}
add_action('plugins_loaded',['Nelson_AI_Assistant','get_instance']);
