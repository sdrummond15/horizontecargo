<?php

namespace AcyMailing\Controllers;

use AcyMailing\Classes\CampaignClass;
use AcyMailing\Classes\MailClass;
use AcyMailing\Classes\MailStatClass;
use AcyMailing\Classes\UrlClickClass;
use AcyMailing\Classes\UserStatClass;
use AcyMailing\Helpers\ExportHelper;
use AcyMailing\Helpers\MailerHelper;
use AcyMailing\Helpers\PaginationHelper;
use AcyMailing\Helpers\TabHelper;
use AcyMailing\Helpers\WorkflowHelper;
use AcyMailing\Libraries\acymController;

class StatsController extends acymController
{
    public function __construct()
    {
        $this->defaulttask = 'globalStats';
        parent::__construct();
        $this->breadcrumb[acym_translation('ACYM_STATISTICS')] = acym_completeLink('stats');
        $this->loadScripts = [
            'all' => ['datepicker', 'thumbnail'],
        ];
        if (acym_getVar('string', 'ctrl', 'dashboard') != 'dashboard') $this->storeAndGetTask();
    }

    private function storeAndGetTask()
    {
        $tasksToStore = [
            'globalStats',
            'detailedStats',
            'clickMap',
            'linksDetails',
            'userClickDetails',
        ];

        if ((empty($this->taskCalled) || $this->taskCalled == 'listing') && !empty($_SESSION['stats_task']) && in_array($_SESSION['stats_task'], $tasksToStore)) {
            $this->{$_SESSION['stats_task']}();
            $this->preventCallTask = true;

            return true;
        }

        if (!empty($this->taskCalled) && !in_array($this->taskCalled, $tasksToStore) && method_exists($this, $this->taskCalled)) {
            $this->{$this->taskCalled}();
            $this->preventCallTask = true;
        } elseif (!empty($this->taskCalled) && $this->taskCalled != 'listing' && in_array($this->taskCalled, $tasksToStore)) {
            $_SESSION['stats_task'] = $this->taskCalled;
        } elseif (!empty($_SESSION['stats_task']) && method_exists($this, $_SESSION['stats_task'])) {
            $this->{$_SESSION['stats_task']}();
            $this->preventCallTask = true;

            return true;
        } else {
            $this->{$this->defaulttask}();
            $this->preventCallTask = true;

            return true;
        }
    }

    public function saveSendingStatUser($userId, $mailId, $sendDate = null)
    {
        $userStatClass = new UserStatClass();

        if ($sendDate == null) {
            $sendDate = acym_date();
        }

        $userStat = new \stdClass();
        $userStat->mail_id = $mailId;
        $userStat->user_id = $userId;
        $userStat->send_date = $sendDate;

        $userStatClass->save($userStat);
    }

    public function prepareDefaultPageInfo(&$data, $needMailId = false)
    {
        $data['workflowHelper'] = new WorkflowHelper();
        $data['selectedMailid'] = $this->getVarFiltersListing('int', 'mail_id', '');

        if ($needMailId && empty($data['selectedMailid'])) {
            $this->globalStats();

            return;
        }

        $mailStatClass = new MailStatClass();
        $data['sentMails'] = $mailStatClass->getAllMailsForStats();
        $data['show_date_filters'] = true;
        $data['page_title'] = false;

        if (acym_isMultilingual()) {
            $multilingualMailSelected = $this->getVarFiltersListing('int', 'mail_id_language', 0);
            if (!empty($multilingualMailSelected)) $data['selectedMailid'] = $multilingualMailSelected;
        }

        $mailClass = new MailClass();
        $data['mailInformation'] = $mailClass->getOneById($data['selectedMailid']);
        if (acym_isMultilingual()) $this->prepareMultilingualMails($data);
    }

    public function globalStats()
    {
        acym_setVar('layout', 'global_stats');

        $data = [];

        $this->prepareDefaultPageInfo($data);

        $this->prepareOpenTimeChart($data);
        $this->preparecharts($data);
        $this->prepareListReceivers($data);
        $this->prepareDefaultRoundCharts($data);
        $this->prepareDefaultLineChart($data);
        $this->prepareDefaultDevicesChart($data);
        $this->prepareDefaultBrowsersChart($data);
        if (acym_isMultilingual()) $this->prepareMultilingualMails($data);
        $this->prepareMailFilter($data);

        parent::display($data);
    }

    public function detailedStats()
    {
        acym_setVar('layout', 'detailed_stats');

        $data = [];

        $this->prepareDefaultPageInfo($data);

        $this->prepareDetailedListing($data);
        $this->prepareMailFilter($data);

        parent::display($data);
    }

    public function clickMap()
    {
        acym_setVar('layout', 'click_map');

        $data = [];

        $this->prepareDefaultPageInfo($data, true);

        $this->prepareClickStats($data);
        $this->prepareMailFilter($data);

        parent::display($data);
    }

    public function linksDetails()
    {
        acym_setVar('layout', 'links_details');

        $data = [];

        $this->prepareDefaultPageInfo($data, true);

        $this->prepareLinksDetailsListing($data);
        $this->prepareMailFilter($data);

        parent::display($data);
    }

    public function userClickDetails()
    {
        acym_setVar('layout', 'user_links_details');

        $data = [];

        $this->prepareDefaultPageInfo($data, true);

        $this->prepareUserLinksDetailsListing($data);
        $this->prepareMailFilter($data);

        parent::display($data);
    }

    private function prepareUserLinksDetailsListing(&$data)
    {
        $data['search'] = $this->getVarFiltersListing('string', 'user_links_details_search', '');
        $data['ordering'] = $this->getVarFiltersListing('string', 'user_links_details_ordering', 'user_id');
        $data['orderingSortOrder'] = $this->getVarFiltersListing('string', 'user_links_details_ordering_sort_order', 'desc');

        if (base64_encode(base64_decode($data['search'])) === $data['search']) {
            $data['search'] = base64_decode($data['search']);
        }

        if (empty($data['selectedMailid'])) return;

        $pagination = new PaginationHelper();
        $urlClickClass = new UrlClickClass();

        $detailedStatsPerPage = $pagination->getListLimit();
        $page = acym_getVar('int', 'user_links_details_pagination_page', 1);

        $userClicks = $urlClickClass->getUserUrlClicksStats(
            [
                'ordering' => $data['ordering'],
                'search' => $data['search'],
                'detailedStatsPerPage' => $detailedStatsPerPage,
                'offset' => ($page - 1) * $detailedStatsPerPage,
                'ordering_sort_order' => $data['orderingSortOrder'],
                'mail_id' => $data['selectedMailid'],
            ]
        );

        $pagination->setStatus($userClicks['total'], $page, $detailedStatsPerPage);

        $data['pagination'] = $pagination;
        $data['user_links_details'] = $userClicks['user_links_details'];
        $data['query'] = $userClicks['query'];
    }

    public function exportUserLinksDetails()
    {
        $this->prepareDefaultPageInfo($data, true);

        $this->prepareUserLinksDetailsListing($data);
        $exportHelper = new ExportHelper();

        $columnsToExport['user.email'] = acym_translation('ACYM_USER');
        $columnsToExport['user_name'] = acym_translation('ACYM_USER_NAME');
        $columnsToExport['url_name'] = acym_translation('ACYM_URL');
        $columnsToExport['date_click'] = acym_translation('ACYM_CLICK_DATE');
        $columnsToExport['click'] = acym_translation('ACYM_TOTAL_CLICKS');

        $exportHelper->exportStatsFullCSV($data['query'], $columnsToExport, 'user_links_details');
        exit;
    }

    private function prepareLinksDetailsListing(&$data)
    {
        $data['search'] = $this->getVarFiltersListing('string', 'links_details_search', '');
        $data['ordering'] = $this->getVarFiltersListing('string', 'links_details_ordering', 'id');
        $data['orderingSortOrder'] = $this->getVarFiltersListing('string', 'links_details_ordering_sort_order', 'desc');

        if (empty($data['selectedMailid'])) return;

        $pagination = new PaginationHelper();
        $urlClickClass = new UrlClickClass();

        $detailedStatsPerPage = $pagination->getListLimit();
        $page = acym_getVar('int', 'links_details_pagination_page', 1);

        $urlClicks = $urlClickClass->getUrlsFromMailsWithDetails(
            [
                'ordering' => $data['ordering'],
                'search' => $data['search'],
                'detailedStatsPerPage' => $detailedStatsPerPage,
                'offset' => ($page - 1) * $detailedStatsPerPage,
                'ordering_sort_order' => $data['orderingSortOrder'],
                'mail_id' => $data['selectedMailid'],
            ]
        );

        $pagination->setStatus($urlClicks['total'], $page, $detailedStatsPerPage);

        $data['pagination'] = $pagination;
        $data['links_details'] = $urlClicks['links_details'];
        $data['query'] = $urlClicks['query'];
    }

    public function exportLinksDetails()
    {
        $this->prepareDefaultPageInfo($data, true);

        $this->prepareLinksDetailsListing($data);
        $exportHelper = new ExportHelper();

        $columnsToExport['url.name'] = acym_translation('ACYM_URL');
        $columnsToExport['total_click'] = acym_translation('ACYM_TOTAL_CLICKS');
        $columnsToExport['unique_click'] = acym_translation('ACYM_UNIQUE_CLICKS');

        $exportHelper->exportStatsFullCSV($data['query'], $columnsToExport, 'links_details');
        exit;
    }

    private function prepareListReceivers(&$data)
    {
        if (empty($data['selectedMailid'])) return;

        $mailStatClass = new MailStatClass();
        $mailClass = new MailClass();

        $data['mailStat'] = $mailStatClass->getOneById($data['selectedMailid']);
        $data['lists'] = $mailClass->getAllListsByMailId($data['selectedMailid']);
    }

    private function prepareDevicesStats(&$data)
    {
        $campaignClass = new CampaignClass();

        $devicesCampaign = $campaignClass->getDevicesWithCountByMailId($data['selectedMailid']);

        $formattedDevices = [];
        foreach ($devicesCampaign as $oneDevice) {
            if (empty($oneDevice->number)) continue;

            if (in_array($oneDevice->device, array_keys(UserStatClass::MOBILE_DEVICES))) {
                $deviceName = UserStatClass::MOBILE_DEVICES[$oneDevice->device];
            } elseif (in_array($oneDevice->device, array_keys(UserStatClass::DESKTOP_DEVICES))) {
                $deviceName = UserStatClass::DESKTOP_DEVICES[$oneDevice->device];
            } else {
                $deviceName = acym_translation('ACYM_UNKNOWN');
            }

            $formattedDevices[$deviceName] = $oneDevice->number;
        }

        $data['devices'] = $formattedDevices;
    }

    private function prepareOpenSourcesStats(&$data)
    {
        $userStatClass = new UserStatClass();
        $openedFromStats = $userStatClass->getOpenSourcesStats($data['selectedMailid']);

        $formattedSources = [];
        foreach ($openedFromStats as $oneSource) {
            if (empty($oneSource->number)) continue;

            if (empty($oneSource->opened_with)) $oneSource->opened_with = acym_translation('ACYM_UNKNOWN');
            $formattedSources[$oneSource->opened_with] = $oneSource->number;
        }

        $data['openedWith'] = $formattedSources;
    }

    private function prepareMultilingualMails(&$data)
    {
        if (empty($data['selectedMailid'])) return;

        $mailClass = new MailClass();

        $translatedEmails = [];

        if (empty($data['mailInformation']->parent_id)) {
            $translatedEmails = $mailClass->getTranslationsById($data['mailInformation']->id, true, true);
        } elseif (!empty($data['mailInformation']->parent_id)) {
            $parentEmail = $mailClass->getOneById($data['mailInformation']->parent_id);
            if (empty($parentEmail)) return;
            $translatedEmails = $mailClass->getTranslationsById($parentEmail->id, true, true);
        }

        $data['emailTranslations'] = [];
        $allLanguages = acym_getLanguages();

        foreach ($translatedEmails as $email) {
            if (!empty($email->language)) $data['emailTranslations'][$email->id] = empty($allLanguages[$email->language]) ? $email->language : $allLanguages[$email->language]->name;
        }
    }

    private function prepareDetailedListing(&$data)
    {
        $data['search'] = $this->getVarFiltersListing('string', 'detailed_stats_search', '');
        $data['ordering'] = $this->getVarFiltersListing('string', 'detailed_stats_ordering', 'send_date');
        $data['orderingSortOrder'] = $this->getVarFiltersListing('string', 'detailed_stats_ordering_sort_order', 'desc');

        if (empty($data['selectedMailid'])) return;

        $userStatClass = new UserStatClass();
        $pagination = new PaginationHelper();

        $detailedStatsPerPage = $pagination->getListLimit();
        $page = acym_getVar('int', 'detailed_stats_pagination_page', 1);

        $matchingDetailedStats = $userStatClass->getDetailedStats(
            [
                'ordering' => $data['ordering'],
                'search' => $data['search'],
                'detailedStatsPerPage' => $detailedStatsPerPage,
                'offset' => ($page - 1) * $detailedStatsPerPage,
                'ordering_sort_order' => $data['orderingSortOrder'],
                'mail_id' => $data['selectedMailid'],
            ]
        );

        $pagination->setStatus($matchingDetailedStats['total'], $page, $detailedStatsPerPage);

        $data['pagination'] = $pagination;
        $data['detailed_stats'] = $matchingDetailedStats['detailed_stats'];
    }

    private function prepareMailFilter(&$data)
    {
        $data['mail_filter'] = acym_select(
            [],
            'mail_id',
            $data['selectedMailid'],
            [
                'class' => 'acym__select acym_select2_ajax',
                'acym-data-default' => acym_translation('ACYM_ALL_EMAILS'),
                'data-placeholder' => acym_translation('ACYM_ALL_EMAILS'),
                'data-ctrl' => 'stats',
                'data-task' => 'searchSentMail',
                'data-min' => '0',
                'data-selected' => $data['selectedMailid'],
            ]
        );

        $data['emailTranslationsFilters'] = '';

        if (!empty($data['emailTranslations'])) {
            $data['emailTranslationsFilters'] = acym_select(
                $data['emailTranslations'],
                'mail_id_language',
                $data['selectedMailid'],
                [
                    'class' => 'acym__select acym__stats__select__language',
                ]
            );
        }
    }

    private function prepareClickStats(&$data)
    {
        if (empty($data['selectedMailid'])) return;

        $urlClickClass = new UrlClickClass();
        $allClickInfo = $urlClickClass->getAllLinkFromEmail($data['selectedMailid']);

        $data['url_click'] = [];
        $data['url_click']['allClick'] = $allClickInfo['allClick'];

        $allPercentage = [];
        foreach ($allClickInfo['urls_click'] as $url) {
            $percentage = 0;
            if (empty($url->click)) {
                $data['url_click'][$url->name] = ['percentage' => $percentage, 'numberClick' => '0'];
            } else {
                $percentage = intval(($url->click * 100) / $allClickInfo['allClick']);
                $data['url_click'][$url->name] = ['percentage' => $percentage, 'numberClick' => $url->click];
            }
            $allPercentage[] = $percentage;
        }

        $helperMailer = new MailerHelper();
        if (!empty($data['mailInformation'])) {
            $helperMailer->body = $data['mailInformation']->body;
            $helperMailer->statClick($data['mailInformation']->id, 0, true);
            $data['mailInformation']->body = $helperMailer->body;
        }


        if (!empty($allPercentage)) {
            $maxPercentage = max($allPercentage);

            foreach ($data['url_click'] as $name => $val) {
                if ($name === 'allClick') continue;
                $percentageRecalc = intval(($val['percentage'] * 100) / $maxPercentage);
                if ($percentageRecalc <= 33) {
                    $data['url_click'][$name]['color'] = '0, 164, 255';
                } elseif ($percentageRecalc <= 66) {
                    $data['url_click'][$name]['color'] = '248, 31, 255';
                } else {
                    $data['url_click'][$name]['color'] = '255, 82, 89';
                }
            }
        }

        $data['url_click'] = json_encode($data['url_click']);

        $data['url_foundation_email'] = ACYM_CSS.'libraries/foundation_email.min.css?v='.filemtime(ACYM_MEDIA.'css'.DS.'libraries'.DS.'foundation_email.min.css');
        $data['url_click_map_email'] = ACYM_CSS.'click_map.min.css?v='.filemtime(ACYM_MEDIA.'css'.DS.'click_map.min.css');
    }

    public function preparecharts(&$data)
    {
        $mailStatClass = new MailStatClass();

        $data['mail'] = $mailStatClass->getOneByMailId($data['selectedMailid']);
        if (empty($data['mail'])) return;

        $campaignClass = new CampaignClass();
        $urlClickClass = new UrlClickClass();

        $data['mail']->totalMail = $data['mail']->sent + $data['mail']->fail;
        $data['mail']->pourcentageSent = empty($data['mail']->totalMail) ? 0 : number_format(($data['mail']->sent * 100) / $data['mail']->totalMail, 2);
        $data['mail']->allSent = empty($data['mail']->totalMail)
            ? acym_translation_sprintf('ACYM_X_MAIL_SUCCESSFULLY_SENT_OF_X', 0, 0)
            : acym_translation_sprintf(
                'ACYM_X_MAIL_SUCCESSFULLY_SENT_OF_X',
                $data['mail']->sent,
                $data['mail']->totalMail
            );

        $openRateCampaign = empty($data['selectedMailid']) ? $campaignClass->getOpenRateAllCampaign() : $campaignClass->getOpenRateOneCampaign($data['selectedMailid']);
        $data['mail']->pourcentageOpen = empty($openRateCampaign->sent) ? 0 : number_format(($openRateCampaign->open_unique * 100) / $openRateCampaign->sent, 2);
        $data['mail']->allOpen = empty($openRateCampaign->sent)
            ? acym_translation_sprintf('ACYM_X_MAIL_OPENED_OF_X', 0, 0)
            : acym_translation_sprintf(
                'ACYM_X_MAIL_OPENED_OF_X',
                $openRateCampaign->open_unique,
                $openRateCampaign->sent
            );

        $clickRateCampaign = $urlClickClass->getNumberUsersClicked($data['selectedMailid']);
        $data['mail']->pourcentageClick = empty($data['mail']->sent) ? 0 : number_format(($clickRateCampaign * 100) / $data['mail']->sent, 2);
        $data['mail']->allClick = empty($data['mail']->sent)
            ? acym_translation_sprintf('ACYM_X_MAIL_CLICKED_OF_X', 0, 0)
            : acym_translation_sprintf(
                'ACYM_X_MAIL_CLICKED_OF_X',
                $clickRateCampaign,
                $data['mail']->sent
            );

        $bounceRateCampaign = empty($data['selectedMailid']) ? $campaignClass->getBounceRateAllCampaign() : $campaignClass->getBounceRateOneCampaign($data['selectedMailid']);
        $data['mail']->pourcentageBounce = empty($data['mail']->sent) ? 0 : number_format(($bounceRateCampaign->bounce_unique * 100) / $data['mail']->sent, 2);
        $data['mail']->allBounce = empty($data['mail']->sent)
            ? acym_translation_sprintf('ACYM_X_BOUNCE_OF_X', 0, 0)
            : acym_translation_sprintf(
                'ACYM_X_BOUNCE_OF_X',
                $bounceRateCampaign->bounce_unique,
                $data['mail']->sent
            );

        if (!empty($data['selectedMailid'])) {
            $mailStatClass = new MailStatClass();
            $mailStat = $mailStatClass->getOneById($data['selectedMailid']);
            $data['mail']->pourcentageUnsub = empty($data['mail']->sent) ? 0 : number_format(($mailStat->unsubscribe_total * 100) / $data['mail']->sent, 2);
            $data['mail']->allUnsub = empty($data['mail']->sent)
                ? acym_translation_sprintf('ACYM_X_USERS_UNSUBSCRIBED_OF_X', 0, 0)
                : acym_translation_sprintf(
                    'ACYM_X_USERS_UNSUBSCRIBED_OF_X',
                    $mailStat->unsubscribe_total,
                    $data['mail']->sent
                );
        }

        $this->prepareDevicesStats($data);
        $this->prepareOpenSourcesStats($data);
        $this->prepareLineChart($data['mail'], $data['selectedMailid']);
    }

    public function prepareDefaultRoundCharts(&$data)
    {
        $charts = [
            'delivery' => [
                'percentage' => 95,
                'text' => 'ACYM_SUCCESSFULLY_SENT',
            ],
            'open' => [
                'percentage' => 25,
                'text' => 'ACYM_OPEN_RATE',
            ],
            'click' => [
                'percentage' => 10,
                'text' => 'ACYM_CLICK_RATE',
            ],
            'fail' => [
                'percentage' => 2,
                'text' => 'ACYM_BOUNCE_RATE',
            ],
            'unsub' => [
                'percentage' => 3,
                'text' => 'ACYM_UNSUBSCRIBE',
            ],
        ];

        $data['example_round_chart'] = '';
        foreach ($charts as $type => $oneChart) {
            if ($type == 'unsub' && empty($data['selectedMailid'])) continue;
            $data['example_round_chart'] .= '<div class="cell acym__stats__donut__one-chart">';
            $data['example_round_chart'] .= acym_round_chart(
                '',
                $oneChart['percentage'],
                $type,
                '',
                acym_translation($oneChart['text'])
            );
            $data['example_round_chart'] .= '</div>';
        }
    }

    public function prepareDefaultLineChart(&$data)
    {
        $dataMonth = [];
        $dataMonth['Jan 18'] = ['open' => '150', 'click' => '40'];
        $dataDay = [];
        $dataDay['23 Jan'] = ['open' => '150', 'click' => '40'];
        $dataHour = [];
        $dataHour['23 Jan 08:00'] = ['open' => '25', 'click' => '10'];
        $dataHour['23 Jan 09:00'] = ['open' => '50', 'click' => '10'];
        $dataHour['23 Jan 10:00'] = ['open' => '16', 'click' => '10'];
        $dataHour['23 Jan 11:00'] = ['open' => '59', 'click' => '10'];
        $data['example_line_chart'] = acym_line_chart('', $dataMonth, $dataDay, $dataHour);
    }

    public function prepareDefaultDevicesChart(&$data)
    {
        $allDevices = array_merge(UserStatClass::DESKTOP_DEVICES, UserStatClass::MOBILE_DEVICES);
        $defaultData = [];

        for ($i = 0 ; $i < 10 ; $i++) {
            $oneDevice = array_rand($allDevices);
            $defaultData[$allDevices[$oneDevice]] = rand(20, 10000);
        }

        $data['example_devices_chart'] = acym_pieChart('', $defaultData, '', acym_translation('ACYM_DEVICES'));
    }

    public function prepareDefaultBrowsersChart(&$data)
    {
        $exampleData = [
            'Google Chrome' => rand(20, 10000),
            'Firefox' => rand(20, 10000),
            'Safari' => rand(20, 10000),
            'Microsoft Edge' => rand(20, 10000),
            'Outlook' => rand(20, 10000),
            'Apple Mail' => rand(20, 10000),
            'Thunderbird' => rand(20, 10000),
        ];

        $data['example_source_chart'] = acym_pieChart('', $exampleData, '', acym_translation('ACYM_OPENED_WITH'));
    }

    public function setDataForChartLine()
    {
        $newStart = acym_date(acym_getVar('string', 'start'), 'Y-m-d H:i:s');
        $newEnd = acym_date(acym_getVar('string', 'end'), 'Y-m-d H:i:s');
        $mailIdOfCampaign = acym_getVar('int', 'id');

        if ($newStart >= $newEnd) {
            echo 'error';
            exit;
        }

        $statsCampaignSelected = new \stdClass();

        $this->prepareLineChart($statsCampaignSelected, $mailIdOfCampaign, $newStart, $newEnd);

        echo @acym_line_chart('', $statsCampaignSelected->month, $statsCampaignSelected->day, $statsCampaignSelected->hour);
        exit;
    }

    private function getValues($modifier, $intervalCode, $campaignOpens, $campaignClicks, $dateCode, $hour = false)
    {
        $opens = [];
        foreach ($campaignOpens as $one) {
            $opens[acym_date(acym_getTime($one->open_date), $dateCode)] = $one->open;
        }

        $clicks = [];
        foreach ($campaignClicks as $one) {
            $clicks[acym_date(acym_getTime($one->date_click), $dateCode)] = $one->click;
        }

        $begin = new \DateTime(empty($campaignClicks) ? $campaignOpens[0]->open_date : min([$campaignOpens[0]->open_date, $campaignClicks[0]->date_click]));
        $end = new \DateTime(empty($campaignClicks) ? end($campaignOpens)->open_date : max([end($campaignOpens)->open_date, end($campaignClicks)->date_click]));

        $end->modify('+1 '.$modifier);

        $interval = new \DateInterval($intervalCode);
        $daterange = new \DatePeriod($begin, $interval, $end);

        $result = [];
        foreach ($daterange as $date) {
            $one = acym_date(acym_getTime($date->format('Y-m-d H:i:s')), $dateCode);

            $current = [];
            $current['open'] = empty($opens[$one]) ? 0 : $opens[$one];
            $current['click'] = empty($clicks[$one]) ? 0 : $clicks[$one];

            $key = $hour ? $one.':00' : $one;
            $result[$key] = $current;
        }

        return $result;
    }

    public function prepareLineChart(&$statsCampaignSelected, $mailIdOfCampaign, $newStart = '', $newEnd = '')
    {
        $campaignClass = new CampaignClass();
        $statsCampaignSelected->hasStats = true;

        $campaignOpenByMonth = $campaignClass->getOpenByMonth($mailIdOfCampaign, $newStart, $newEnd);
        $campaignOpenByDay = $campaignClass->getOpenByDay($mailIdOfCampaign, $newStart, $newEnd);
        $campaignOpenByHour = $campaignClass->getOpenByHour($mailIdOfCampaign, $newStart, $newEnd);

        if (empty($campaignOpenByMonth) || empty($campaignOpenByDay) || empty($campaignOpenByHour)) {
            $statsCampaignSelected->hasStats = false;

            return;
        }

        $urlClickClass = new UrlClickClass();
        $campaignClickByMonth = $urlClickClass->getAllClickByMailMonth($mailIdOfCampaign, $newStart, $newEnd);
        $campaignClickByDay = $urlClickClass->getAllClickByMailDay($mailIdOfCampaign, $newStart, $newEnd);
        $campaignClickByHour = $urlClickClass->getAllClickByMailHour($mailIdOfCampaign, $newStart, $newEnd);

        $statsCampaignSelected->month = $this->getValues('day', 'P1M', $campaignOpenByMonth, $campaignClickByMonth, 'Y-m');
        $statsCampaignSelected->day = $this->getValues('hour', 'P1D', $campaignOpenByDay, $campaignClickByDay, 'Y-m-d');
        $statsCampaignSelected->hour = $this->getValues('min', 'PT1H', $campaignOpenByHour, $campaignClickByHour, 'Y-m-d H:i', true);

        $allHour = array_keys($statsCampaignSelected->hour);

        $statsCampaignSelected->startEndDateHour = [];
        $statsCampaignSelected->startEndDateHour['start'] = $allHour[0];
        $statsCampaignSelected->startEndDateHour['end'] = end($allHour);
    }

    public function searchSentMail()
    {
        $idSelected = acym_getVar('int', 'id', 0);
        if (!empty($idSelected)) {
            $mailClass = new MailClass();
            $mail = $mailClass->getOneById($idSelected);
            $name = empty($mail->name) ? '' : $mail->name;

            echo json_encode(
                [
                    'value' => $idSelected,
                    'text' => $name,
                ]
            );
            exit;
        }

        $return = [];
        $search = acym_getVar('string', 'search', '');

        $mailstatClass = new MailStatClass();
        $mails = $mailstatClass->getAllMailsForStats($search);

        foreach ($mails as $oneMail) {
            $return[] = [$oneMail->id, $oneMail->name];
        }

        echo json_encode($return);
        exit;
    }

    private function exportGlobalFormatted()
    {
        $exportHelper = new ExportHelper();
        $data['selectedMailid'] = acym_getVar('int', 'mail_id', '');
        $data['show_date_filters'] = true;
        $data['page_title'] = false;
        $timeLinechart = acym_getVar('string', 'time_linechart', 'month');

        if (acym_isMultilingual()) {
            $multilingualMailSelected = acym_getVar('int', 'mail_id_language', 0);
            if (!empty($multilingualMailSelected)) $data['selectedMailid'] = $multilingualMailSelected;
        }

        $this->prepareMailFilter($data);
        $this->prepareClickStats($data);
        $this->preparecharts($data);
        $this->prepareDefaultRoundCharts($data);
        $this->prepareDefaultLineChart($data);
        $this->prepareDefaultDevicesChart($data);
        $this->prepareDefaultBrowsersChart($data);

        $globalDonut = [
            $data['mail']->pourcentageSent,
            $data['mail']->pourcentageOpen,
            $data['mail']->pourcentageClick,
            $data['mail']->pourcentageBounce,
            $data['mail']->pourcentageUnsub,
        ];
        $mailName = empty($data['selectedMailid']) ? acym_translation('ACYM_ALL_MAILS') : $data['mailInformation']->name;
        $globalLine = $data['mail']->$timeLinechart;

        $exportHelper->exportStatsFormattedCSV($mailName, $globalDonut, $globalLine, $timeLinechart);
        exit;
    }

    private function exportGlobalFull()
    {
        $exportHelper = new ExportHelper();
        $selectedMailid = acym_getVar('int', 'mail_id', '');

        if (acym_isMultilingual()) {
            $multilingualMailSelected = acym_getVar('int', 'mail_id_language', 0);
            if (!empty($multilingualMailSelected)) $data['selectedMailid'] = $multilingualMailSelected;
        }

        $where = '';
        if (!empty($selectedMailid)) $where = 'WHERE mail_id = '.intval($selectedMailid);

        $columnsMailStat = acym_getColumns('mail_stat');
        $columnsToExport = [];

        $columnsToExport['mail.subject'] = acym_translation('ACYM_EMAIL_SUBJECT');
        foreach ($columnsMailStat as $column) {
            if (in_array($column, ['mail_id'])) continue;
            $trad = acym_translation('ACYM_'.strtoupper($column).'_COLUMN_STAT');
            if ($column == 'send_date') $trad = acym_translation('ACYM_SEND_DATE');
            $columnsToExport['mailstat.'.$column] = $trad;
        }

        $query = 'SELECT '.implode(', ', array_keys($columnsToExport)).' FROM #__acym_mail_stat AS mailstat LEFT JOIN #__acym_mail AS mail ON mail.id = mailstat.mail_id '.$where;
        $exportHelper->exportStatsFullCSV($query, $columnsToExport);
        exit;
    }

    public function exportDetailed()
    {
        $exportHelper = new ExportHelper();
        $selectedMailid = acym_getVar('int', 'mail_id', '');

        if (acym_isMultilingual()) {
            $multilingualMailSelected = acym_getVar('int', 'mail_id_language', 0);
            if (!empty($multilingualMailSelected)) $data['selectedMailid'] = $multilingualMailSelected;
        }

        $where = '';
        if (!empty($selectedMailid)) $where = 'WHERE userstat.`mail_id` = '.intval($selectedMailid);

        $groupBy = ' GROUP BY userstat.mail_id, userstat.user_id ';

        $columnsMailStat = acym_getColumns('user_stat');
        $columnsToExport = [];

        $columnsToExport['mail.subject'] = acym_translation('ACYM_EMAIL_SUBJECT');
        $columnsToExport['user.email'] = acym_translation('ACYM_USER_EMAIL');
        $columnsToExport['user.name'] = acym_translation('ACYM_USER_NAME');
        foreach ($columnsMailStat as $column) {
            if (in_array($column, ['user_id', 'mail_id'])) continue;
            $trad = acym_translation('ACYM_'.strtoupper($column).'_COLUMN_STAT');
            if ($column == 'send_date') $trad = acym_translation('ACYM_SEND_DATE');
            if ($column == 'open') $trad = acym_translation('ACYM_OPEN_TOTAL_COLUMN_STAT');
            if ($column == 'bounce') $trad = acym_translation('ACYM_BOUNCE_UNIQUE_COLUMN_STAT');
            $columnsToExport['userstat.'.$column] = $trad;
        }

        $query = 'SELECT '.implode(', ', array_keys($columnsToExport)).', SUM(urlclick.click) AS click FROM #__acym_user_stat AS userstat 
                  LEFT JOIN #__acym_user AS user ON user.id = userstat.user_id 
                  LEFT JOIN #__acym_mail AS mail ON mail.id = userstat.mail_id 
                  LEFT JOIN #__acym_url_click AS urlclick ON urlclick.user_id = userstat.user_id AND userstat.mail_id = urlclick.mail_id  '.$where.$groupBy;
        $columnsToExport['urlclick.click'] = acym_translation('ACYM_TOTAL_CLICK');
        $exportHelper->exportStatsFullCSV($query, $columnsToExport, 'detailed');
        exit;
    }

    public function exportGlobal()
    {
        $exportType = acym_getVar('string', 'export_type', 'charts');

        $functionName = 'exportGlobal'.ucfirst($exportType);

        if (!method_exists($this, $functionName)) {
            acym_enqueueMessage(acym_translation('ACYM_EXPORT_METHOD_NOT_FOUND'), 'error');
            $this->listing();

            return;
        }

        $this->$functionName();
    }

    public function prepareOpenTimeChart(&$data)
    {
        $userStatClass = new UserStatClass();
        $statsDB = $userStatClass->getOpenTimeStats($data['selectedMailid']);

        if (empty($statsDB['total_open'])) {
            $data['openTime'] = $userStatClass->getDefaultStat();
            $data['empty_open'] = true;

            return true;
        }
        $data['empty_open'] = false;

        $stats = [];

        for ($day = 0 ; $day < 7 ; $day++) {
            $stats[$day] = [];
            for ($hour = 0 ; $hour < 8 ; $hour++) {
                if (empty($statsDB['stats'][$day.'_'.$hour]) || empty($statsDB['total_open'])) {
                    $percentage = 0;
                } else {
                    $percentage = ($statsDB['stats'][$day.'_'.$hour]->open_total * 100) / $statsDB['total_open'];
                }
                $stats[$day][$hour] = round($percentage);
            }
        }

        $data['openTime'] = $stats;

        return true;
    }
}
