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

    public static function myKeys(string $username): array
    {
        return MinecartAPI::send("/shop/player/mykeys", [
            "username" => $username
        ]);
    }

    public static function redeemCash(string $username): array
    {
        return MinecartAPI::send("/shop/player/redeemcash", [
            "username" => $username
        ]);
    }

    public static function redeemKey(string $username, string $key)
    {
        return MinecartAPI::send("/shop/player/redeemcash", [
            "username" => $username,
            "key" => $key
        ]);
    }

    public static function deliveryPending(): array
    {
        $result = MinecartAPI::send("/shop/delivery/pending");

        return $result["response"]["products"] ?? [];
    }

    public static function deliveryConfirm(array $products): bool
    {
        $result = MinecartAPI::send("/shop/delivery/confirm", [
            "products" => $products
        ]);

        return $result["statusCode"] == 200;
    }

    private static function send(string $url, array $params = []): array
    {
        try {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, MinecartAPI::BASE_URL . $url);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HTTPHEADER, [
                "Authorization: " . Minecart::getInstance()->getCfg("Minecart.ShopKey"),
                "ShopServer: " . Minecart::getInstance()->getCfg("Minecart.ShopServer"),
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
