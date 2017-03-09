<?php

$index_url = 'https://www.ptt.cc/bbs/Baseball/index%d.html';
$start_index = 5056; // 這邊放 PTT 的列表頁 Live 文出現在第幾頁
$subject_pattern = '#\[LIVE\] 台灣vs荷蘭\s+(.*) (\d*):(\d*)#i'; # 標題的格式
$col_map = array(
    'score_a' => 2,
    'score_b' => 3,
    'inning' => 1,
);

date_default_timezone_set('Asia/Taipei');
$records = array();
for ($d = $start_index; ; $d++) {
    $content = file_get_contents(sprintf($index_url, $d));
    $doc = new DOMDocument;
    @$doc->loadHTML($content);
    $empty = true;
    foreach ($doc->getElementsByTagName('a') as $a_dom) {
        $subject = trim($a_dom->nodeValue);
        if (!preg_match($subject_pattern, $subject, $matches)) {
            continue;
        }
        $empty = false;

        $record = new StdClass;
        $record->url = 'https://www.ptt.cc' . $a_dom->getAttribute('href');

        foreach ($col_map as $k => $pos) {
            $record->{$k} = $matches[$pos];
        }
        $doc_article = new DOMDocument;
        @$doc_article->loadHTML(file_get_contents($record->url));

        // 抓文章時間
        foreach ($doc_article->getElementsByTagName('span') as $span_dom) {
            if ($span_dom->getAttribute('class') == 'article-meta-tag' and $span_dom->nodeValue == '時間') {
                $record->time = strtotime($span_dom->nextSibling->nodeValue);
                break;
            }
        }

        $record->pushes = array();

        foreach ($doc_article->getElementsByTagName('div') as $div_dom) {
            if ($div_dom->getAttribute('class') != 'push') {
                continue;
            }
            $push = new StdClass;
            $push->text = trim($div_dom->nodeValue);
            foreach ($div_dom->getElementsByTagName('span') as $span_dom) {
                if ($span_dom->getAttribute('class') == 'push-ipdatetime') {
                    $push->time = strtotime('2017/' . trim($span_dom->nodeValue) . ':00');
                    break;
                }
            }
            $record->pushes[] = $push;
        }

        $records[] = $record;
    }
    if ($empty) {
        break;
    }
}

echo json_encode($records, JSON_UNESCAPED_UNICODE);
