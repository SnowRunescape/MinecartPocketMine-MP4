<?php

namespace Minecart\utils;

use Minecart\Minecart;

class API
{
    private $authorization;
    private $shopServer;

    const URI = "https://api.minecart.com.br";
    const MYKEYS_URI = self::URI . "/shop/player/mykeys";
    const REDEEMKEY_URI = self::URI . "/shop/player/redeemkey";
    const REDEEMCASH_URI = self::URI . "/shop/player/redeemcash";

    const DELIVERY_PENDING_URI = self::URI . "/shop/delivery/pending";
    const DELIVERY_CONFIRM_URI = self::URI . "/shop/delivery/confirm";

    const INVALID_KEY = 40010;
    const INVALID_SHOP_SERVER = 40011;
    const DONT_HAVE_CASH = 40012;
    const COMMANDS_NOT_REGISTRED = 40013;

    const DELAY = 60 * 20;

    private $url;
    private $params;

    public function setParams(array $params): void
    {
        $this->params = $params;
    }

    public function setURL(string $url): void
    {
        $this->url = $url;
    }

    public function setAuthorization(string $authorization): void
    {
        $this->authorization = $authorization;
    }

    public function setShopServer(string $shopServer): void
    {
        $this->shopServer = $shopServer;
    }

    public function send(): array
    {
        try {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $this->url);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HTTPHEADER, [
                "Authorization: {$this->authorization}",
                "ShopServer: {$this->shopServer}",
                "PluginVersion: " . Minecart::VERSION,
                "Content-Type: application/x-www-form-urlencoded"
            ]);
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($this->params));
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

            $response = curl_exec($curl);
            $response = json_decode($response, true);

            $response = [
                "statusCode" => curl_getinfo($curl)["http_code"],
                "response" => $response
            ];

            if (empty($response["response"])) {
                $response["statusCode"] = 500;
            }

            curl_close($curl);
        } catch(\Exception $exception) {
            $response = [
                "statusCode" => 500,
                "response" => [
                    "code" => 329832
                ]
            ];
        }

        return $response;
    }
}
