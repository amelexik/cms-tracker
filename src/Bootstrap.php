<?php

namespace skeeks\modules\cms\tracker;

use skeeks\modules\cms\tracker\components\TrackerComponent;
use Yii;
use yii\base\BootstrapInterface;
use yii\helpers\Url;
use yii\web\View;

class Bootstrap implements BootstrapInterface
{
    /**
     * @param \yii\base\Application $app
     */
    public function bootstrap($app)
    {
        if ($this->getIsBackend())
            return;

        if ($app->tracker->badAgent())
            return;


        $app->view->on(View::EVENT_BEGIN_BODY, function ($event) {

            // todo доработать проверку разрешения трекинга в cmsContent->is_count_views

            if ((bool)Yii::$app->tracker->enabled !== false) {
                if (Yii::$app->response->statusCode == 200) {
                    $url = Yii::$app->request->pathInfo;
                    if (Yii::$app->tracker->trackingMode == TrackerComponent::TRACK_MODE_AJAX) {
                        $ajaxUrl = Url::to('/tracker/service/track-url');
                        Yii::$app->view->registerJs(<<<JS
                    var data = {};
                    if($('meta[name=csrf-param]').length && $('meta[name=csrf-token]').length){
                        data[$('meta[name=csrf-param]').attr('content')] = $('meta[name=csrf-token]').attr('content');
                    }
                    
                    $.ajax({
                        type:'POST',
                        data:data,
                        url:'{$ajaxUrl}'
                    });
JS
                        );
                    } else {
                        Yii::$app->tracker->trackUrl($url);
                    }
                }
            }
        });

    }

    /**
     * @return bool
     * check backend
     */
    public function getIsBackend()
    {
        return strpos(Yii::$app->request->pathInfo, '~sx') !== false;
    }


}