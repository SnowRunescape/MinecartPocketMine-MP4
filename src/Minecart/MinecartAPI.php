<?php

namespace Minecart;

class MinecartAPI
{
    const BASE_URL = "https://api.minecart.com.br";

    const INVALID_KEY = 40010;
    const INVALID_SHOP_SERVER = 40011;
    const DONT_HAVE_CASH = 40012;
    const COMMANDS_NOT_REGISTRED = 40013;

    const DELAY = 60 * 20;

    private MinecartAuthorizationAPI $minecartAuthorizationAPI;

    public function __construct(MinecartAuthorizationAPI $minecartAuthorizationAPI)
    {
        $this->minecartAuthorizationAPI = $minecartAuthorizationAPI;
    }

    public function myKeys(string $username): array
    {
        return $this->send("/shop/player/mykeys", [
            "username" => $username
        ]);
    }

    public function redeemCash(string $username): array
    {
        return $this->send("/shop/player/redeemcash", [
            "username" => $username
        ]);
    }

    public function redeemKey(string $username, string $key)
    {
        return $this->send("/shop/player/redeemcash", [
            "username" => $username,
            "key" => $key
        ]);
    }

    public function deliveryPending(): array
    {
        $result = $this->send("/shop/delivery/pending");

        return $result["response"]["products"] ?? [];
    }

    public function deliveryConfirm(array $products): bool
    {
        $result = $this->send("/shop/delivery/confirm", [
            "products" => $products
        ]);

        return $result["statusCode"] == 200;
    }

    private function send(string $url, array $params = []): array
    {
        try {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, self::BASE_URL . $url);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HTTPHEADER, [
                "Authorization: {$this->minecartAuthorizationAPI->getAuthorization()}",
                "ShopServer: {$this->minecartAuthorizationAPI->getShopServer()}",
                "PluginVersion: " . Minecart::VERSION,
                "Content-Type: application/x-www-form-urlencoded"
            ]);
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($params));
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
        } catch (\Exception $exception) {
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
