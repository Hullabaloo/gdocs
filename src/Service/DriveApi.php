<?php

namespace App\Service;

class DriveApi
{
    private $filesService;

    public function __construct(FilesService $filesService)
    {
        $this->filesService = $filesService;
    }

    /**
     * Request valid access token from google API
     * using service account key file
     * @return array
     */
    public function requestAuthToken(): array
    {
        $jwtHeaderArr = [
            "alg" => "RS256",
            "typ" => "JWT"
        ];

        $jwtClaimArr = [
            "iss" => $_ENV['GOOGLE_SERVICEACC_EMAIL'],
            "scope" => "https://www.googleapis.com/auth/drive",
            "aud" => "https://oauth2.googleapis.com/token",
            "exp" => time() + 3600,
            "iat" => time()
        ];

        $jwtHeader = $this->base64urlEncode(json_encode($jwtHeaderArr));
        $jwtClaim = $this->base64urlEncode(json_encode($jwtClaimArr));
        $key = $_ENV['GOOGLE_SERVICEACC_KEY'];

        $pkeyid = openssl_pkey_get_private($key);
        openssl_sign($jwtHeader . '.' . $jwtClaim, $jwtSignature, $pkeyid, "sha256");
        $jwtSignature = $this->base64urlEncode($jwtSignature);
        $jwt = $jwtHeader . '.' . $jwtClaim . '.' . $jwtSignature;

        $url = 'https://oauth2.googleapis.com/token';
        $query = 'grant_type=urn%3Aietf%3Aparams%3Aoauth%3Agrant-type%3Ajwt-bearer&assertion=' . $jwt;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
        $response = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if (!$err) {
            $r = json_decode($response);
            if ($r->access_token)
                return ['err' => 0, 'token' => $r->access_token];
            if (isset($r->error)) {
                return ['err' => 1, 'message' => $r->error];
            }
            return ['err' => 1, 'message' => 'unknown error'];
        }
        return ['err' => 1, 'message' => 'unknown error'];
    }

    /**
     * URL safe base64_encode
     * @param string $data
     * @return string
     */
    public function base64urlEncode(string $data): string
    {
        return str_replace('=', '', strtr(base64_encode($data), '+/', '-_'));
    }

    /**
     * Get files from $folderId folder only
     * @param string $accessToken
     * @param string $folderId
     * @return array
     */
    public function driveGetFilesInFolder(string $accessToken, string $folderId): array
    {
        $query = "parents in '".$folderId."'";
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => 'https://www.googleapis.com/drive/v3/files?q='.urlencode($query),
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer ' . $accessToken
            ),
            CURLOPT_POST => 0,
            CURLOPT_RETURNTRANSFER => 1
        ]);
        $response = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if (!$err) {
            $r = json_decode($response);
            if ($r->files)
                return ['err' => 0, 'files' => $r->files];
            if (isset($r->error)) {
                return ['err' => 1, 'message' => $r->error];
            }
        }
        return ['err' => 1, 'message' => 'No new files found'];
    }


    /**
     * Download CSV files
     * @param string $accessToken
     * @param \stdClass $file
     * @param string $path
     * @return string
     */
    public function fileDownloadCSV(string $accessToken, \stdClass $file, string $path): string
    {
        //echo $file->name . PHP_EOL;
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => 'https://www.googleapis.com/drive/v2/files/' . $file->id . '?alt=media',
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer ' . $accessToken
            ),
            CURLOPT_POST => 0,
            CURLOPT_RETURNTRANSFER => 1
        ]);
        $response = curl_exec($ch);
        $err = curl_error($ch);

        curl_close($ch);
        return $response;
    }

    /**
     * Download google sheet files by converting to CSV (requires export API method)
     * @param string $accessToken
     * @param \stdClass $file
     * @param string $path
     * @return string
     */
    public function fileDownloadGoogleSheets(string $accessToken, \stdClass $file, string $path): string
    {
        //echo $file->name . PHP_EOL;
        $ch = curl_init();
        //mimeType=text/csv
        curl_setopt_array($ch, [
            CURLOPT_URL => 'https://www.googleapis.com/drive/v3/files/' . $file->id . '/export?alt=media&' . http_build_query(['mimeType' => 'text/csv']),
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer ' . $accessToken
            ),
            CURLOPT_POST => 0,
            CURLOPT_RETURNTRANSFER => 1
        ]);
        $response = curl_exec($ch);
        $err = curl_error($ch);

        curl_close($ch);
        return $response;
    }

    /**
     * Move file from $currentFolderId to $newFolderId
     * @param string $accessToken
     * @param string $fileId
     * @param string $currentFolderId
     * @param string $newFolderId
     */
    public function fileMove(string $accessToken, string $fileId, string $currentFolderId, string $newFolderId): void
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => 'https://www.googleapis.com/drive/v3/files/'.$fileId.'?removeParents='.$currentFolderId.'&addParents='.$newFolderId ,
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer ' . $accessToken,
                'Content-Type: application/json'
            ),
            CURLOPT_CUSTOMREQUEST => "PATCH",
            CURLOPT_RETURNTRANSFER => 1,
        ]);
        $response = curl_exec($ch);
        $err = curl_error($ch);

        curl_close($ch);
    }

    public function getElementIdByName(string $accessToken, string $query)
    {
        $ch = curl_init();
        //$q = "name = 'upload'";
        //$q = "parents in '1EDy46Ot6DZnGkQRFHvtGCXNsBSOonzHJ'";
        curl_setopt_array($ch, [
            CURLOPT_URL => 'https://www.googleapis.com/drive/v3/files?q='.urlencode("name ='".$query."'"),
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer ' . $accessToken
            ),
            CURLOPT_POST => 0,
            CURLOPT_RETURNTRANSFER => 1
        ]);
        $response = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if (!$err) {
            $r = json_decode($response);
            if ($r->files)
                return ['err' => 0, 'id' => $r->files[0]->id];
            if (isset($r->error)) {
                return ['err' => 1, 'message' => $r->error];
            }
        }
        return ['err' => 1, 'message' => 'unknown error when getting files from Google Drive'];
    }


}
