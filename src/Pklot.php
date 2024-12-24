<?php
/**
 * 作者:郭磊
 * 邮箱:174000902@qq.com
 * 电话:15210720528
 * Git:https://github.com/guolei19850528/laravel-tiehu
 */
namespace Guolei19850528\Laravel\Tiehu;

use GuzzleHttp\RequestOptions;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

/**
 * @see https://www.showdoc.com.cn/1735808258920310/9467753400037587
 */
class Pklot
{
    /**
     * @var string
     */
    protected string $baseUrl = '';
    /**
     * @var string
     */
    protected string $appKey = '';

    /**
     * @var string|int
     */
    protected string|int $parkingId = '';

    public function getBaseUrl(): string
    {
        if (\str($this->baseUrl)->endsWith('/')) {
            return \str($this->baseUrl)->substr(0, -1)->toString();
        }
        return $this->baseUrl;
    }

    public function setBaseUrl(string $baseUrl): Pklot
    {
        $this->baseUrl = $baseUrl;
        return $this;
    }

    public function getAppKey(): string
    {
        return $this->appKey;
    }

    public function setAppKey(string $appKey): Pklot
    {
        $this->appKey = $appKey;
        return $this;
    }

    public function getParkingId(): int|string
    {
        return $this->parkingId;
    }

    public function setParkingId(int|string $parkingId): Pklot
    {
        $this->parkingId = $parkingId;
        return $this;
    }

    public function __construct(string|int $parkingId = '', string $appKey = '', string $baseUrl = '')
    {
        $this->setParkingId($parkingId);
        $this->setAppKey($appKey);
        $this->setBaseUrl($baseUrl);
    }


    public function signature(array|Collection $data = []): string
    {
        $sorted = \collect($data)->forget('appKey')->toArray();
        ksort($sorted);
        return \str(\md5(
            \str(
                http_build_query(
                    $sorted
                )
            )->append(
                \str(\md5($this->getAppKey()))->upper()->toString()
            )->toString()
        ))->upper()->toString();
    }

    /**
     * @param string|null $method request method
     * @param string|null $url request url
     * @param array|Collection|null $urlParameters request urlParameters
     * @param array|Collection|null $data request data
     * @param array|Collection|null $query request query
     * @param array|Collection|null $headers request headers
     * @param array|Collection|null $options request options
     * @param \Closure|null $responseHandler response handler
     * @return mixed
     * @throws \Exception
     */
    public function requestWithSignature(
        string|null           $method = 'GET',
        string|null           $url = '',
        array|Collection|null $urlParameters = [],
        array|Collection|null $data = [],
        array|Collection|null $query = [],
        array|Collection|null $headers = [],
        array|Collection|null $options = [],
        \Closure|null         $responseHandler = null
    ): mixed
    {
        $method = \str($method)->isEmpty() ? 'GET' : $method;
        $data = \collect($data);
        $query = \collect($query);
        $headers = \collect($headers);
        $urlParameters = \collect($urlParameters);
        $options = \collect($options);
        \data_fill($data, 'parkingId', $this->getParkingId());
        \data_fill($data, 'timestamp', \now()->timestamp * 1000);
        \data_fill($data, 'sign', $this->signature($data));
        \data_fill($options, RequestOptions::FORM_PARAMS, $data->toArray());
        \data_fill($options, RequestOptions::QUERY, $query->toArray());
        $response = Http::baseUrl($this->getBaseUrl())
            ->withHeaders($headers->toArray())
            ->withUrlParameters($urlParameters->toArray())
            ->send($method, $url, $options->toArray());
        if ($responseHandler instanceof \Closure) {
            return \value($responseHandler($response));
        }
        if ($response->ok()) {
            $json = $response->json();
            if (Validator::make($json, ['status' => 'required|integer|size:1'])->messages()->isEmpty()) {
                return \collect(\json_decode(\data_get($json, 'Data', ''), true));
            }
        }
        return \collect();
    }

}
