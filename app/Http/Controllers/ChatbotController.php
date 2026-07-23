<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Order;
use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class ChatbotController extends Controller
{
    public function ask(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $userMessage  = trim($request->message);
        $messageLower = strtolower($userMessage);

        // ─────────────────────────────────────────
        // Deteksi topik pertanyaan
        // ─────────────────────────────────────────
        $isProductQuestion =
            str_contains($messageLower, 'produk') ||
            str_contains($messageLower, 'product') ||
            str_contains($messageLower, 'kategori') ||
            str_contains($messageLower, 'category') ||
            str_contains($messageLower, 'katagori') ||
            str_contains($messageLower, 'stok') ||
            str_contains($messageLower, 'stock') ||
            str_contains($messageLower, 'harga') ||
            str_contains($messageLower, 'price') ||
            str_contains($messageLower, 'sembako') ||
            str_contains($messageLower, 'bumbu') ||
            str_contains($messageLower, 'dapur') ||
            str_contains($messageLower, 'rumah tangga') ||
            str_contains($messageLower, 'kebutuhan') ||
            str_contains($messageLower, 'jual') ||
            str_contains($messageLower, 'katalog') ||
            str_contains($messageLower, 'barang') ||
            str_contains($messageLower, 'item') ||
            str_contains($messageLower, 'menu') ||
            str_contains($messageLower, 'tersedia') ||
            str_contains($messageLower, 'ada') ||
            str_contains($messageLower, 'berapa');

        $isOrderQuestion =
            str_contains($messageLower, 'pesanan') ||
            str_contains($messageLower, 'order') ||
            str_contains($messageLower, 'status') ||
            str_contains($messageLower, 'pengiriman') ||
            str_contains($messageLower, 'bayar') ||
            str_contains($messageLower, 'resi');

        $isCartQuestion =
            str_contains($messageLower, 'keranjang') ||
            str_contains($messageLower, 'cart') ||
            str_contains($messageLower, 'belanjaan') ||
            str_contains($messageLower, 'checkout');

        // ─────────────────────────────────────────
        // Data statistik & kategori — selalu fresh
        // ─────────────────────────────────────────
        $totalActiveProducts = Product::where('status', 'active')->count();

        $categories = Category::withCount(['products' => fn($q) => $q->where('status', 'active')])
            ->get()
            ->map(fn($c) => [
                'kategori' => $c->category_name,
                'jumlah'   => $c->products_count . ' produk',
            ])
            ->toArray();

        $products = [];

        if ($isProductQuestion) {
            $products = Product::where('status', 'active')
                ->with('category')
                ->latest('updated_at')
                ->limit(50)
                ->get()
                ->map(fn($p) => [
                    'nama'     => $p->name,
                    'kategori' => $p->category?->category_name ?? 'Umum',
                    'harga'    => 'Rp ' . number_format($p->price, 0, ',', '.'),
                    'stok'     => $p->stock_quantity . ' ' . ($p->stock_unit ?? 'pcs'),
                ])
                ->toArray();
        }

        // ─────────────────────────────────────────
        // Data pesanan user
        // ─────────────────────────────────────────
        $orderData = [];

        if ($isOrderQuestion && Auth::check()) {
            $latestOrder = Order::where('user_id', Auth::id())
                ->latest()
                ->first();

            if ($latestOrder) {
                $orderData = [
                    'kode_pesanan'      => $latestOrder->order_code,
                    'status_pesanan'    => $latestOrder->order_status,
                    'status_pembayaran' => $latestOrder->payment_status,
                    'metode_pembayaran' => $latestOrder->payment_method,
                    'total_belanja'     => 'Rp ' . number_format($latestOrder->grand_total, 0, ',', '.'),
                    'alamat_pengiriman' => $latestOrder->shipping_address,
                ];
            }
        }

        // ─────────────────────────────────────────
        // Data keranjang user
        // ─────────────────────────────────────────
        $cartData = [];

        if ($isCartQuestion && Auth::check()) {
            $cart = Cart::where('user_id', Auth::id())
                ->with(['items.product', 'items.variant'])
                ->first();

            if ($cart && $cart->items->count() > 0) {
                $subtotal  = $cart->items->sum(fn($item) =>
                    ($item->variant ? $item->variant->price : $item->product->price) * $item->quantity
                );

                $cartData = [
                    'jumlah_item' => $cart->items->count(),
                    'items'       => $cart->items->map(fn($item) => [
                        'produk' => $item->product->name ?? 'Produk',
                        'varian' => $item->variant->variant_name ?? null,
                        'jumlah' => $item->quantity,
                        'harga'  => 'Rp ' . number_format(
                            $item->variant ? $item->variant->price : $item->product->price,
                            0, ',', '.'
                        ),
                    ])->toArray(),
                    'subtotal' => 'Rp ' . number_format($subtotal, 0, ',', '.'),
                ];
            } else {
                $cartData = ['status' => 'Keranjang kosong'];
            }
        }

        // ─────────────────────────────────────────
        // Konteks toko
        // ─────────────────────────────────────────
        $storeContext = [
            'nama_toko'             => 'Toko Tika',
            'deskripsi'             => 'Toko UMKM modern untuk kebutuhan harian.',
            'jam_buka'              => '08:00 - 18:00 WIB',
            'alamat'                => 'Pasar Rawa Kalong, Bekasi',
            'kontak'                => '0821-2505-2233',
            'total_produk_tersedia' => $totalActiveProducts . ' produk',
            'kategori'              => $categories,
            'daftar_produk'         => $products,
            'pesanan_user'          => $orderData,
            'keranjang'             => $cartData,
        ];

        // ─────────────────────────────────────────
        // System Prompt
        // ─────────────────────────────────────────
        $systemPrompt = <<<PROMPT
Kamu adalah asisten AI customer service Toko Tika.

Aturan:
1. Jawab dalam Bahasa Indonesia.
2. Jawab singkat, jelas, ramah, natural.
3. Maksimal 3-5 kalimat kecuali user meminta detail.
4. Jika user bertanya berapa total jumlah produk yang ada di toko, gunakan data 'total_produk_tersedia' (yaitu {$totalActiveProducts} produk).
5. Jangan mengarang stok atau harga — gunakan data yang diberikan dalam konteks.
6. Gunakan konteks toko, produk, pesanan, dan keranjang jika relevan.
7. Jika ada data keranjang, bantu user memahami isi belanjaannya.
8. Ingat konteks percakapan sebelumnya untuk menjawab dengan baik.
9. Kalau tidak tahu, jujur dan sarankan hubungi admin.
PROMPT;

        // ─────────────────────────────────────────
        // ✅ MEMORY: Ambil history dari session
        // ─────────────────────────────────────────
        $sessionKey = 'chatbot_history_' . (Auth::check() ? Auth::id() : session()->getId());
        $history    = session($sessionKey, []);

        // Tambah pesan user ke history
        $history[] = ['role' => 'user', 'content' => $userMessage];

        // Batasi 10 pesan terakhir agar tidak terlalu panjang
        if (count($history) > 10) {
            $history = array_slice($history, -10);
        }

        // Susun messages: system + context + history (sudah termasuk pesan user terbaru)
        $inputMessages = array_merge(
            [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'system', 'content' => 'Data toko: ' . json_encode($storeContext, JSON_UNESCAPED_UNICODE)],
            ],
            $history
        );

        // ─────────────────────────────────────────
        // Request ke OpenRouter
        // ─────────────────────────────────────────
        try {
            $response = Http::retry(2, 1000)
                ->timeout(15)
                ->acceptJson()
                ->withHeaders([
                    'Authorization' => 'Bearer ' . config('services.openrouter.api_key'),
                    'HTTP-Referer'  => config('app.url'),
                    'X-Title'       => config('app.name'),
                ])
                ->post('https://openrouter.ai/api/v1/chat/completions', [
                    'model'       => config('services.openrouter.model', 'deepseek/deepseek-chat-v3-0324'),
                    'messages'    => $inputMessages,
                    'max_tokens'  => 200,
                    'temperature' => 0.7,
                ]);

        } catch (\Throwable $e) {
            Log::error('Chatbot request gagal', ['message' => $e->getMessage()]);
            return response()->json(['reply' => 'Maaf, chatbot sedang gangguan. Coba lagi sebentar ya.'], 500);
        }

        if (!$response->successful()) {
            Log::error('OpenRouter error', ['status' => $response->status()]);
            return response()->json(['reply' => 'Maaf, AI sedang sibuk. Coba lagi sebentar.'], 500);
        }

        $reply = data_get($response->json(), 'choices.0.message.content');
        $reply = trim($reply) ?: 'Maaf, saya belum bisa menjawab itu.';

        // ✅ MEMORY: Simpan balasan AI ke history
        $history[] = ['role' => 'assistant', 'content' => $reply];

        // Simpan history ke session (per user / guest session)
        session([$sessionKey => $history]);

        return response()->json(['reply' => $reply]);
    }

    // ─────────────────────────────────────────
    // ✅ Reset history chat (opsional)
    // Panggil via: POST /chatbot/reset
    // ─────────────────────────────────────────
    public function reset()
    {
        $sessionKey = 'chatbot_history_' . (Auth::check() ? Auth::id() : session()->getId());
        session()->forget($sessionKey);
        return response()->json(['status' => 'ok']);
    }
}