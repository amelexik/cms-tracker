<?php

namespace skeeks\modules\cms\tracker\components;

use skeeks\cms\base\Component;
use skeeks\cms\models\CmsContentElement;
use skeeks\cms\models\CmsTree;
use skeeks\cms\modules\admin\widgets\formInputs\OneImage;
use skeeks\modules\cms\tracker\models\CmsTrackerHits;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;

/**
 * Class TrackerComponent
 * @package skeeks\modules\cms\tracker\components
 */
class TrackerComponent extends Component
{
    /**
     * Можно задать название и описание компонента
     * @return array
     */
    static public function descriptorConfig()
    {
        return array_merge(parent::descriptorConfig(), [
            'name' => \Yii::t('skeeks/tracker', 'Cms tracker setting'),
        ]);
    }

    /**
     *
     */
    const HIT_TYPE_VIEW_COUNT = 1;
    /**
     *
     */
    const HIT_TYPE_COMMENT_COUNT = 2;

    /**
     *
     */
    const TRACK_MODE_DEFAULT = 0;
    /**
     *
     */
    const TRACK_MODE_AJAX = 1;

    /**
     * @var bool
     */
    public $enabled = false;
    /**
     * @var int
     */
    public $trackingMode = self::TRACK_MODE_DEFAULT;
    /**
     * @var int
     */
    public $cookieLifeTime = 1800;


    /**
     * @var bool
     */
    public $enableSyncGA = false;
    /**
     * @var int
     */
    public $syncFrequencyTime = 1600;

    /**
     * @var int
     */
    public $syncBeginFrom = 100;
    /**
     * @var
     */
    public $gaProfileId;
    /**
     * @var
     */
    public $gaApiKey;


    /**
     * @return array
     */
    public static function getTrackingMode()
    {
        return [
            self::TRACK_MODE_DEFAULT => \Yii::t('skeeks/tracker', 'Default mode'),
            self::TRACK_MODE_AJAX    => \Yii::t('skeeks/tracker', 'Use ajax'),
        ];
    }

    /**
     * @return array
     */
    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [['enabled'], 'boolean'],
            [['trackingMode'], 'number'],
            [['cookieLifeTime'], 'number'],
            [['syncFrequencyTime'], 'number'],
            [['syncBeginFrom'], 'number'],
            [['enableSyncGA'], 'boolean'],
            [['gaProfileId'], 'string'],
            [['gaApiKey'], 'string']
        ]);
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'enabled'           => \Yii::t('skeeks/tracker', 'Enable Tracking'),
            'trackingMode'      => \Yii::t('skeeks/tracker', 'Tracking mode'),
            'cookieLifeTime'    => \Yii::t('skeeks/tracker', 'Cookie Life Time'),
            'syncFrequencyTime' => \Yii::t('skeeks/tracker', 'Sync Frequency (in seconds)'),
            'syncBeginFrom'     => \Yii::t('skeeks/tracker', 'Start sync from count'),
            'enableSyncGA'      => \Yii::t('skeeks/tracker', 'Enable synchronize with Google Analytics'),
            'gaProfileId'       => \Yii::t('skeeks/tracker', 'Google Analytic Profile Id'),
            'gaApiKey'          => \Yii::t('skeeks/tracker', 'Google Analytic Api Key'),
        ]);
    }

    /**
     * @return array
     */
    public function attributeHints()
    {
        return ArrayHelper::merge(parent::attributeHints(), [
            'enabled'        => \Yii::t('skeeks/tracker', 'This option disables and enables the operation of the entire component. Turning off all its other settings will not be taken into account.'),
            'gaApiKey'       => \Yii::t('skeeks/tracker', 'Valid .json file'),
            'cookieLifeTime' => \Yii::t('skeeks/tracker', 'Time (in seconds) after which the user’s visit is defined as a new visit'),
        ]);
    }


    /**
     * @param ActiveForm $form
     * @return string|void
     */
    public function renderConfigFormFields(ActiveForm $form)
    {

        $result = $form->fieldSet(\Yii::t('skeeks/tracker', 'Main'));
        $result .= $form->field($this, 'enabled')->radioList(\Yii::$app->formatter->booleanFormat);
        $result .= $form->fieldSetEnd();

        $result .= $form->fieldSet(\Yii::t('skeeks/tracker', 'Tracking Setting'));
        $result .= $form->field($this, 'trackingMode')->radioList(self::getTrackingMode());
        $result .= $form->field($this, 'cookieLifeTime')->textInput();
        $result .= $form->fieldSetEnd();

        $result .= $form->fieldSet(\Yii::t('skeeks/tracker', 'Synchronize with Google Analytics'));
        $result .= $form->field($this, 'enableSyncGA')->radioList(\Yii::$app->formatter->booleanFormat);
        $result .= $form->field($this, 'syncBeginFrom')->textInput();
        $result .= $form->field($this, 'syncFrequencyTime')->textInput();
        $result .= $form->field($this, 'gaProfileId')->textInput();
        $result .= $form->field($this, 'gaApiKey')->widget(OneImage::className(), ['showPreview' => false]);


        $result .= $form->fieldSetEnd();

        return $result;
    }


    /**
     * @param $url
     * @return bool
     * todo проверить на bad agent
     */
    public function trackUrl($url)
    {
        if (!$url)
            return false;

        if ($this->badAgent())
            return false;

        $this->updateViewCount($url);

    }


    /**
     * @param $url
     * @return bool
     */
    public function updateViewCount($url)
    {
        if (!$url)
            return false;

        if (preg_match('/\d+$/', $url, $matches)) {
            if ($id = (int)$matches[0]) {
                if ($model = CmsContentElement::find()->where(['id' => $id])->active()->one()) {

                    $cookiesKey = md5($url);


                    if (\Yii::$app->request->cookies->get($cookiesKey, false)) {
                        return false;
                    }

                    // получение коллекции (yii\web\CookieCollection) из компонента "response"
                    $cookies = \Yii::$app->response->cookies;

                    // добавление новой куки в HTTP-ответ
                    $cookies->add(new \yii\web\Cookie([
                        'name'   => $cookiesKey,
                        'value'  => true,
                        'expire' => time() + \Yii::$app->tracker->cookieLifeTime
                    ]));

                    $this->updateHits($model);

                    return true;
                }
            }
        }else{

            if ($model = CmsTree::find()->where(['dir' => $url])->one()) {

                $cookiesKey = md5($url);


                if (\Yii::$app->request->cookies->get($cookiesKey, false)) {
                    return false;
                }

                // получение коллекции (yii\web\CookieCollection) из компонента "response"
                $cookies = \Yii::$app->response->cookies;

                // добавление новой куки в HTTP-ответ
                $cookies->add(new \yii\web\Cookie([
                    'name'   => $cookiesKey,
                    'value'  => true,
                    'expire' => time() + \Yii::$app->tracker->cookieLifeTime
                ]));

                $this->updateHits($model);

                return true;
            }
        }
    }


    /**
     * @param $model
     * @param int $type
     * @return array|CmsTrackerHits|null
     */
    public function updateHits($model, $type = self::HIT_TYPE_VIEW_COUNT)
    {
        $model_class = get_class($model);
        $pk = $model->primaryKey;

        if ($hitModel = CmsTrackerHits::find()->where(['type' => $type, 'model_class' => $model_class, 'pk' => $pk])->one()) {
            if ($this->enableSyncGA && $type = self::HIT_TYPE_VIEW_COUNT) {
                if ($this->canSyncGa($hitModel)) {
                    $view_count = $this->pageViewsGa('ga:pageviews',
                        'ga:pagePath',
                        'ga:pagePath=~' . $model->getUrl()
                    );
                    if ((int)$view_count > 0) {
                        $hitModel->hits = $view_count;
                        $hitModel->sync_at = time();
                        $hitModel->save();
                        return $hitModel;
                    }
                }
            }
            $hitModel->updateCounters(['hits' => 1]);
        } else {
            $hitModel = new CmsTrackerHits();
            $hitModel->attributes = [
                'type'        => $type,
                'model_class' => $model_class,
                'pk'          => $pk,
                'hits'        => 1
            ];
            $hitModel->save();
        }
        return $hitModel;
    }

    public function getHits($model, $type = self::HIT_TYPE_VIEW_COUNT)
    {
        $model_class = get_class($model);
        $pk = $model->primaryKey;

        if ($hitModel = CmsTrackerHits::find()->where(['type' => $type, 'model_class' => $model_class, 'pk' => $pk])->one()) {
            return $hitModel->hits;
        }
        return null;

    }


    public function canSyncGa(CmsTrackerHits $model)
    {
        if ($this->enableSyncGA && (int)$this->syncFrequencyTime > 0) {
            if ((int)$this->syncBeginFrom > 0 && $model->hits < (int)$this->syncBeginFrom) {
                return false;
            }
            if (!$model->sync_at)
                return true;

            if (time() > $model->sync_at + $this->syncFrequencyTime)
                return true;

            return false;
        }

        return false;
    }


    /**
     * @return bool
     */
    static public function badAgent()
    {
        $userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';


        if (!$userAgent
            || strpos($userAgent, 'Bot') !== false
            || strpos($userAgent, 'bot') !== false
            || (strpos($userAgent, 'Mozilla') === false
                && strpos($userAgent, 'Opera') === false
                && strpos($userAgent, 'SAMSUNG') === false)) {

            return true;
        }

        return false;
    }


    public function pageViewsGa($metrics = 'ga:pageviews', $dimensions = 'ga:pagePath', $filters = '')
    {

        $profileId = \Yii::$app->tracker->gaProfileId;
        if (empty($profileId)) {
            return true;
        }

        $key_path = \Yii::$app->tracker->gaApiKey;
        if (empty($key_path)) {
            return true;
        }
        $key_path = \Yii::getAlias('@root') . $key_path;
        if (!preg_match('/\.json/', $key_path) || !file_exists($key_path)) {
            return true;
        }
        //Для успешной работы необходимо
        //1.Создать проект и активировать аналитик АПИ https://console.developers.google.com
        //2.Создать сервисный аккаунт с json ключом
        //get service account key https://console.developers.google.com/iam-admin/serviceaccounts
        //3.В гугл аналитике дать доступ сервисному аккаунту на просмотр: https://analytics.google.com/analytics/web


        putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $key_path);

        $client = new \Google_Client();
        //only service account
        $client->useApplicationDefaultCredentials();

        if (preg_match('/\/(\d+)-/', $filters, $matches, PREG_OFFSET_CAPTURE)) {
            $filters = substr_replace($filters, '[0-9]+', $matches[1][1], strlen($matches[1][0]));
        }
        $filters = substr($filters, 0, 128 + 13); // 13 == strlen('ga:pagePath=~')

        $optParams = [
            'dimensions' => $dimensions,
            'filters'    => $filters
        ];

        $client->setScopes(['https://www.googleapis.com/auth/analytics.readonly']);

        $analytics = new \Google_Service_Analytics($client);

        try {
            $results = $analytics->data_ga->get(
                'ga:' . $profileId, '2015-01-01', 'today', $metrics, $optParams
            );
            $rows = $results->getRows();
            \Yii::info('GA-results: ' . print_r($rows, true));
            $result = null;
            if (is_array($rows)) {
                foreach ($rows as $row) {
                    $result += $row[1];
                }
            }
            return $result;
        } catch (\Exception $e) {
            //Handle API service exceptions.
            \Yii::error('GA-errors: ' . $e->getMessage());
            return null;
        }
    }

}