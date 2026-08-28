<?php namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Config\Services;

class JWTAuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $authHeader = $request->getHeaderLine('Authorization');

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return Services::response()->setJSON([
                'status' => false,
                'message' => 'Authorization header tidak ditemukan atau format salah'
            ])->setStatusCode(401);
        }

        $token = trim(str_replace('Bearer', '', $authHeader));
        $key = getenv('JWT_SECRET') ?: 'your-secret-key';

        try {
            $decoded = JWT::decode($token, new Key($key, 'HS256'));
            // Simpan user ke dalam request
            $request->user = $decoded;
        } catch (\Exception $e) {
            return Services::response()->setJSON([
                'status' => false,
                'message' => 'Token tidak valid',
                'error' => $e->getMessage()
            ])->setStatusCode(401);
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Tidak diperlukan untuk sekarang
    }
}
