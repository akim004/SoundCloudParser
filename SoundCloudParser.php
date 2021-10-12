<?php

require_once 'DbRepository.php';

class SoundCloudParser
{
    private $clientId;

    private $tracks;

    private $repository;

    private $url = 'https://api-v2.soundcloud.com/';

    private $parseUrl;

    public function __construct($parseUrl, DbRepository $repository)
    {
        $this->parseUrl   = $parseUrl;
        $this->repository = $repository;
        $this->clientId   = $this->getClientId();

        if (!$this->clientId) {
            throw new \Exception("Client id not found");
        }

    }

    public function getClientId()
    {
        $html = $this->getBodyByUrl($this->parseUrl);
        $html = $this->getBodyByUrl('https://a-v2.sndcdn.com/assets/48-35914b0a.js');

        if (preg_match('/query\:\{client_id:\"(.*?)\"\}\}/', $html, $result) === 1) {
            return $result[1];
        }

        return;
    }

    public function getTracks($userId)
    {
        $params = [
            'client_id' => $this->clientId,
            'limit'     => 100,
            'offset'    => 0,
        ];

        if ($data = $this->sendRequest('users/' . $userId . '/tracks', $params)) {
            return $data['collection'];
        }

        return [];
    }

    public function parse()
    {
        $userInfo = [];
        $tracks   = [];

        $html = $this->getBodyByUrl($this->parseUrl);

        if ($userInfo = $this->getUserInfo($html)) {
            $tracks = $this->getTracks($userInfo['id']);
        }

        return [
            'user'   => $userInfo,
            'tracks' => $tracks,
        ];
    }

    protected function getUserInfo($html)
    {
        if (preg_match('/window.__sc_hydration = (.*?);\<\/script\>/', $html, $result) === 1) {
            $objects = json_decode($result[1], true);
            foreach ($objects as $object) {
                if ($object['hydratable'] == 'user') {
                    $user = $object['data'];
                }
            }
            return $user;
        }

        return;
    }

    protected function getBodyByUrl($url)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_PROXYTYPE, 'HTTP');

        $body = curl_exec($curl);
        $res  = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        $error = curl_error($curl);
        curl_close($curl);

        if ($res !== 200) {
            throw new Exception($error);
        }

        return $body;
    }

    protected function sendRequest($action, $reqParams = [])
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $this->url . $action);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        if (count($reqParams) > 0) {
            curl_setopt(
                $curl,
                CURLOPT_URL,
                $this->url . $action . '?' . http_build_query(
                    $reqParams
                )
            );
        }

        $out = curl_exec($curl);
        $res = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        $error = curl_error($curl);
        curl_close($curl);

        if ($res !== 200) {
            throw new Exception($error);
        }

        $data = json_decode($out, true);

        return $data;
    }

    public function saveData($data)
    {
        if ($user = $this->repository->saveUser($data['user'])) {
            if ($this->repository->saveTracks($user['id'], $data['tracks'])) {
                return true;
            }
        }

        return false;
    }
}
