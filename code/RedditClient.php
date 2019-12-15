<?php

class RedditClient
{
    private $clientId;
    private $clientSecret;
    private $accessToken;
    private $tokenType;

    /**
     * RedditClient constructor.
     * @param string $clientId
     * @param string $clientSecret
     */
    public function __construct(string $clientId, string $clientSecret)
    {
        $this->clientId     = $clientId;
        $this->clientSecret = $clientSecret;
    }

    /**
     * @param string $username
     * @param string $password
     * @return bool
     */
    public function login(string $username, string $password) : bool
    {
        $params = array(
            'grant_type' => 'password',
            'username' => $username,
            'password' => $password
        );

        // curl settings and call to reddit
        $ch = curl_init('https://www.reddit.com/api/v1/access_token');
        curl_setopt($ch, CURLOPT_USERPWD, $this->clientId . ':' . $this->clientSecret);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

        // curl response from reddit
        $response_raw = curl_exec($ch);
        $response     = json_decode($response_raw);
        curl_close($ch);

        if (!empty($response->access_token)) {
            $this->accessToken = $response->access_token;
            $this->tokenType   = $response->token_type ?? 'bearer';
            return true;
        }
        return false;
    }

    /**
     * @param string $endpoint
     * @param array $params
     * @return array|null
     */
    private function callAPI(string $endpoint, array $params) : array
    {
        $apiCallEndpoint = 'https://oauth.reddit.com' . $endpoint . '?' . http_build_query($params);

        $ch = curl_init($apiCallEndpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Simple PHP client for Reddit');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array( "Authorization: " . $this->tokenType . " " . $this->accessToken));
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

        // curl response from our post call
        $response_raw = curl_exec($ch);
        $response     = json_decode($response_raw, true);

        return $this->parseResults($response ?? []);
    }

    /**
     * @param string $sub
     * @param int $limit
     * @param int $count
     * @return array|null
     */
    public function getPopularPosts(int $limit = 50, int $count = 0) : array
    {
        if ($limit < 25) {
            $limit = 25;
        } elseif ($limit > 100) {
            $limit = 100;
        }
        if ($count < 0) {
            $count = 0;
        }

        $params = [
            'g' => 'GLOBAL',
            'count' => $count,
            'limit' => $limit,
        ];

        return $this->callAPI('/r/popular', $params);
    }

    /**
     * @param array $results
     * @return array
     */
    private function parseResults(array $results) : array
    {
        $output = [];
        if (!empty($results['data'])) {
            $data = $results['data'];
            if (!empty($data['children'])) {
                foreach ($data['children'] as $post) {
                    $output[] = [
                        'author'   => $post['data']['author'],
                        'sub'      => $post['data']['subreddit_name_prefixed'],
                        'headline' => $post['data']['title'],
                        'content'  => $post['data']['selftext'],
                        'url'      => $post['data']['url'],
                    ];
                }
            }
        }

        return $output;
    }
}