<?php

namespace ccxt;

// PLEASE DO NOT EDIT THIS FILE, IT IS GENERATED AND WILL BE OVERWRITTEN:
// https://github.com/ccxt/ccxt/blob/master/CONTRIBUTING.md#how-to-contribute-code

use Exception as Exception; // a common import

class btcchina extends Exchange {

    public function describe () {
        return array_replace_recursive (parent::describe (), array (
            'id' => 'btcchina',
            'name' => 'BTCChina',
            'countries' => array ( 'CN' ),
            'rateLimit' => 1500,
            'version' => 'v1',
            'has' => array (
                'CORS' => true,
            ),
            'urls' => array (
                'logo' => 'https://user-images.githubusercontent.com/1294454/27766368-465b3286-5ed6-11e7-9a11-0f6467e1d82b.jpg',
                'api' => array (
                    'plus' => 'https://plus-api.btcchina.com/market',
                    'public' => 'https://data.btcchina.com/data',
                    'private' => 'https://api.btcchina.com/api_trade_v1.php',
                ),
                'www' => 'https://www.btcchina.com',
                'doc' => 'https://www.btcchina.com/apidocs',
            ),
            'api' => array (
                'plus' => array (
                    'get' => array (
                        'orderbook',
                        'ticker',
                        'trade',
                    ),
                ),
                'public' => array (
                    'get' => array (
                        'historydata',
                        'orderbook',
                        'ticker',
                        'trades',
                    ),
                ),
                'private' => array (
                    'post' => array (
                        'BuyIcebergOrder',
                        'BuyOrder',
                        'BuyOrder2',
                        'BuyStopOrder',
                        'CancelIcebergOrder',
                        'CancelOrder',
                        'CancelStopOrder',
                        'GetAccountInfo',
                        'getArchivedOrder',
                        'getArchivedOrders',
                        'GetDeposits',
                        'GetIcebergOrder',
                        'GetIcebergOrders',
                        'GetMarketDepth',
                        'GetMarketDepth2',
                        'GetOrder',
                        'GetOrders',
                        'GetStopOrder',
                        'GetStopOrders',
                        'GetTransactions',
                        'GetWithdrawal',
                        'GetWithdrawals',
                        'RequestWithdrawal',
                        'SellIcebergOrder',
                        'SellOrder',
                        'SellOrder2',
                        'SellStopOrder',
                    ),
                ),
            ),
            'markets' => array (
                'BTC/CNY' => array( 'id' => 'btccny', 'symbol' => 'BTC/CNY', 'base' => 'BTC', 'quote' => 'CNY', 'api' => 'public', 'plus' => false ),
                'LTC/CNY' => array( 'id' => 'ltccny', 'symbol' => 'LTC/CNY', 'base' => 'LTC', 'quote' => 'CNY', 'api' => 'public', 'plus' => false ),
                'LTC/BTC' => array( 'id' => 'ltcbtc', 'symbol' => 'LTC/BTC', 'base' => 'LTC', 'quote' => 'BTC', 'api' => 'public', 'plus' => false ),
                'BCH/CNY' => array( 'id' => 'bcccny', 'symbol' => 'BCH/CNY', 'base' => 'BCH', 'quote' => 'CNY', 'api' => 'plus', 'plus' => true ),
                'ETH/CNY' => array( 'id' => 'ethcny', 'symbol' => 'ETH/CNY', 'base' => 'ETH', 'quote' => 'CNY', 'api' => 'plus', 'plus' => true ),
            ),
        ));
    }

    public function fetch_markets ($params = array ()) {
        $request = array (
            'market' => 'all',
        );
        $markets = $this->publicGetTicker (array_merge ($request, $params));
        $result = array();
        $keys = is_array($markets) ? array_keys($markets) : array();
        for ($i = 0; $i < count ($keys); $i++) {
            $key = $keys[$i];
            $market = $markets[$key];
            $parts = explode('_', $key);
            $id = $parts[1];
            $baseId = mb_substr ($id, 0, 3);
            $quoteId = mb_substr ($id, 3, 6);
            $base = strtoupper($baseId);
            $quote = strtoupper($quoteId);
            $base = $this->common_currency_code($base);
            $quote = $this->common_currency_code($quote);
            $symbol = $base . '/' . $quote;
            $result[] = array (
                'id' => $id,
                'symbol' => $symbol,
                'base' => $base,
                'quote' => $quote,
                'baseId' => $baseId,
                'quoteId' => $quoteId,
                'info' => $market,
            );
        }
        return $result;
    }

    public function fetch_balance ($params = array ()) {
        $this->load_markets();
        $response = $this->privatePostGetAccountInfo ($params);
        $balances = $this->safe_value($response, 'result');
        $result = array( 'info' => $balances );
        $codes = is_array($this->currencies) ? array_keys($this->currencies) : array();
        for ($i = 0; $i < count ($codes); $i++) {
            $code = $codes[$i];
            $currency = $this->currency ($code);
            $account = $this->account ();
            $currencyId = $currency['id'];
            if (is_array($balances['balance']) && array_key_exists($currencyId, $balances['balance'])) {
                $account['total'] = floatval ($balances['balance'][$currencyId]['amount']);
            }
            if (is_array($balances['frozen']) && array_key_exists($currencyId, $balances['frozen'])) {
                $account['used'] = floatval ($balances['frozen'][$currencyId]['amount']);
            }
            $account['free'] = $account['total'] - $account['used'];
            $result[$code] = $account;
        }
        return $this->parse_balance($result);
    }

    public function create_market_request ($market) {
        $request = array();
        $field = ($market['plus']) ? 'symbol' : 'market';
        $request[$field] = $market['id'];
        return $request;
    }

    public function fetch_order_book ($symbol, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $method = $market['api'] . 'GetOrderbook';
        $request = $this->create_market_request ($market);
        $response = $this->$method (array_merge ($request, $params));
        $timestamp = $this->safe_integer($response, 'date');
        if ($timestamp !== null) {
            $timestamp *= 1000;
        }
        return $this->parse_order_book($response, $timestamp);
    }

    public function parse_ticker ($ticker, $market) {
        $timestamp = $this->safe_integer($ticker, 'date');
        if ($timestamp !== null) {
            $timestamp *= 1000;
        }
        $last = $this->safe_float($ticker, 'last');
        return array (
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'high' => $this->safe_float($ticker, 'high'),
            'low' => $this->safe_float($ticker, 'low'),
            'bid' => $this->safe_float($ticker, 'buy'),
            'ask' => $this->safe_float($ticker, 'sell'),
            'vwap' => $this->safe_float($ticker, 'vwap'),
            'open' => $this->safe_float($ticker, 'open'),
            'close' => $last,
            'last' => $last,
            'previousClose' => null,
            'change' => null,
            'percentage' => null,
            'average' => null,
            'baseVolume' => $this->safe_float($ticker, 'vol'),
            'quoteVolume' => null,
            'info' => $ticker,
        );
    }

    public function parse_ticker_plus ($ticker, $market) {
        $timestamp = $this->safe_integer($ticker, 'Timestamp');
        $symbol = null;
        if ($market !== null) {
            $symbol = $market['symbol'];
        }
        return array (
            'symbol' => $symbol,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'high' => $this->safe_float($ticker, 'High'),
            'low' => $this->safe_float($ticker, 'Low'),
            'bid' => $this->safe_float($ticker, 'BidPrice'),
            'ask' => $this->safe_float($ticker, 'AskPrice'),
            'vwap' => null,
            'open' => $this->safe_float($ticker, 'Open'),
            'last' => $this->safe_float($ticker, 'Last'),
            'change' => null,
            'percentage' => null,
            'average' => null,
            'baseVolume' => $this->safe_float($ticker, 'Volume24H'),
            'quoteVolume' => null,
            'info' => $ticker,
        );
    }

    public function fetch_ticker ($symbol, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $method = $market['api'] . 'GetTicker';
        $request = $this->create_market_request ($market);
        $response = $this->$method (array_merge ($request, $params));
        $ticker = $this->safe_value($response, 'ticker');
        if ($market['plus']) {
            return $this->parse_ticker_plus ($ticker, $market);
        }
        return $this->parse_ticker($ticker, $market);
    }

    public function parse_trade ($trade, $market) {
        $timestamp = $this->safe_integer($trade, 'date');
        if ($timestamp !== null) {
            $timestamp *= 1000;
        }
        $price = $this->safe_float($trade, 'price');
        $amount = $this->safe_float($trade, 'amount');
        $cost = null;
        if ($amount !== null) {
            if ($price !== null) {
                $cost = $amount * $price;
            }
        }
        $id = $this->safe_string($trade, 'tid');
        return array (
            'id' => $id,
            'info' => $trade,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'symbol' => $market['symbol'],
            'type' => null,
            'side' => null,
            'price' => $price,
            'amount' => $amount,
            'cost' => $cost,
        );
    }

    public function parse_trade_plus ($trade, $market) {
        $timestamp = $this->parse8601 ($this->safe_string($trade, 'timestamp'));
        $price = $this->safe_float($trade, 'price');
        $amount = $this->safe_float($trade, 'size');
        $cost = null;
        if ($amount !== null) {
            if ($price !== null) {
                $cost = $amount * $price;
            }
        }
        $side = $this->safe_string($trade, 'side');
        if ($side !== null) {
            $side = strtolower($side);
        }
        return array (
            'id' => null,
            'info' => $trade,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'symbol' => $market['symbol'],
            'type' => null,
            'side' => $side,
            'price' => $price,
            'amount' => $amount,
            'cost' => $cost,
        );
    }

    public function parse_trades_plus ($trades, $market = null) {
        $result = array();
        for ($i = 0; $i < count ($trades); $i++) {
            $result[] = $this->parse_trade_plus ($trades[$i], $market);
        }
        return $result;
    }

    public function fetch_trades ($symbol, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $method = $market['api'] . 'GetTrade';
        $request = $this->create_market_request ($market);
        if ($market['plus']) {
            $now = $this->milliseconds ();
            $request['start_time'] = $now - 86400 * 1000;
            $request['end_time'] = $now;
        } else {
            $method .= 's'; // trades vs trade
        }
        $response = $this->$method (array_merge ($request, $params));
        if ($market['plus']) {
            return $this->parse_trades_plus ($response['trades'], $market);
        }
        return $this->parse_trades($response, $market, $since, $limit);
    }

    public function create_order ($symbol, $type, $side, $amount, $price = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $method = 'privatePost' . $this->capitalize ($side) . 'Order2';
        $request = array();
        $id = strtoupper($market['id']);
        if ($type === 'market') {
            $request['params'] = array ( null, $amount, $id );
        } else {
            $request['params'] = array ( $price, $amount, $id );
        }
        $response = $this->$method (array_merge ($request, $params));
        $orderId = $this->safe_string($response, 'id');
        return array (
            'info' => $response,
            'id' => $orderId,
        );
    }

    public function cancel_order ($id, $symbol = null, $params = array ()) {
        $this->load_markets();
        $market = $params['market']; // TODO fixme
        $request = array (
            'params' => array ( $id, $market ),
        );
        return $this->privatePostCancelOrder (array_merge ($request, $params));
    }

    public function nonce () {
        return $this->microseconds ();
    }

    public function sign ($path, $api = 'public', $method = 'GET', $params = array (), $headers = null, $body = null) {
        $url = $this->urls['api'][$api] . '/' . $path;
        if ($api === 'private') {
            $this->check_required_credentials();
            $p = array();
            if (is_array($params) && array_key_exists('params', $params)) {
                $p = $params['params'];
            }
            $nonce = $this->nonce ();
            $request = array (
                'method' => $path,
                'id' => $nonce,
                'params' => $p,
            );
            $p = implode(',', $p);
            $body = $this->json ($request);
            $query = implode('&', array('tonce=' . $nonce,
                'accesskey=' . $this->apiKey,
                'requestmethod=' . strtolower($method),
                'id=' . $nonce,
                'method=' . $path,
                'params=' . $p,));
            $signature = $this->hmac ($this->encode ($query), $this->encode ($this->secret), 'sha1');
            $auth = $this->encode ($this->apiKey . ':' . $signature);
            $headers = array (
                'Authorization' => 'Basic ' . base64_encode ($auth),
                'Json-Rpc-Tonce' => $nonce,
            );
        } else {
            if ($params) {
                $url .= '?' . $this->urlencode ($params);
            }
        }
        return array( 'url' => $url, 'method' => $method, 'body' => $body, 'headers' => $headers );
    }
}
