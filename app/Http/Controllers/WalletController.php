<?php

namespace App\Http\Controllers;

use App\Data\WalletStoreData;
use App\Data\WalletUpdateData;
use App\Models\Category;
use App\Models\Wallet;
use App\Services\WalletService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Transaction;
use App\Models\WalletTransfer;

class WalletController extends Controller
{
    public function __construct(
        protected WalletService $service
    )
    {
    }

    public function index(Request $request): JsonResponse
    {
        abort_if(!$request->user(), 401, 'Unauthorized');
        return response()->json($this->service->all($request->user()));
    }

    public function store(WalletStoreData $data): JsonResponse
    {
        $wallet = $this->service->create($data);
        return response()->json($wallet, 201);
    }

    public function show(Wallet $wallet): JsonResponse
    {
        return response()->json($this->service->show($wallet));
    }

    public function update(WalletUpdateData $data, Wallet $wallet): JsonResponse
    {
        return response()->json($this->service->update($wallet, $data));
    }

    public function destroy(Request $request,Wallet $wallet): JsonResponse
    {
        if ($wallet->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized');
        }
        try {
            $this->service->delete($wallet);
            return response()->json(null, 204);
        }catch (Exception $exception){
            return response()->json([
                'message' => $exception->getMessage()
            ], 500);
        }
    }

    public function transactions(Request $request, Wallet $wallet): JsonResponse
    {
        $page = $request->query('page', 1);
        return response()->json($this->service->getTransactions($wallet, $page));
    }

    public function transfer(Request $request)
    {
        $request->validate([
            'fromWalletId' => 'required|exists:wallets,id',
            'toWalletId' => 'required|exists:wallets,id|different:fromWalletId',
            'amount' => 'required|numeric|min:0.01',
            'note' => 'nullable|string|max:255'
        ]);

        $fromWallet = Wallet::where('user_id', auth()->id())
            ->findOrFail($request->fromWalletId);
        $toWallet = Wallet::where('user_id', auth()->id())
            ->findOrFail($request->toWalletId);

        if ($fromWallet->balance < $request->amount) {
            return response()->json([
                'message' => 'Insufficient balance in source wallet'
            ], 422);
        }

        // if ($fromWallet->currency !== $toWallet->currency) {
        //     return response()->json([
        //         'message' => 'Cannot transfer between wallets with different currencies'
        //     ], 422);
        // }

        DB::transaction(function () use ($request, $fromWallet, $toWallet) {
            $fromWallet->decrement('balance', $request->amount);
            $toWallet->increment('balance', $request->amount);

            // Record the transfer
            WalletTransfer::create([
                'user_id' => auth()->id(),
                'from_wallet_id' => $fromWallet->id,
                'to_wallet_id' => $toWallet->id,
                'amount' => $request->amount,
                'note' => $request->note,
                'transferred_at' => now(),
            ]);

            $this->service->recordBalanceHistory($fromWallet->id, $fromWallet->balance);
            $this->service->recordBalanceHistory($toWallet->id, $toWallet->balance);
        });

        return response()->json([
            'message' => 'Transfer completed successfully'
        ]);
    }

    public function balanceHistory($walletId)
    {
        $wallet = Wallet::where('user_id', auth()->id())
            ->findOrFail($walletId);

        $history = $this->service->getBalanceHistory($wallet);

        return response()->json($history);
    }

    public function transfers(Request $request): JsonResponse
    {
        $transfers = WalletTransfer::where('user_id', auth()->id())
            ->with(['fromWallet:id,name,type', 'toWallet:id,name,type'])
            ->orderBy('transferred_at', 'desc')
            ->paginate(15);

        return response()->json($transfers);
    }
}
