<?php

namespace Minecart;

class MinecartAuthorizationAPI
{
    private string $authorization;
    private string $shopServer;

    public function __construct(string $authorization, string $shopServer)
    {
        $this->authorization = $authorization;
        $this->shopServer = $shopServer;
    }

    public function getAuthorization(): string
    {
        return $this->authorization;
    }

    public function getShopServer(): string
    {
        return $this->shopServer;
    }
}
