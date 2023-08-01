<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function getMessage(Request $request) {
        return response()->json($this->prepareData($request, 'getMessage'));
    }

    public function createMessage(Request $request) {
        return response()->json($this->prepareData($request, 'createMessage'));
    }

    public function updateMessage(Request $request) {
        return response()->json($this->prepareData($request, 'updateMessage'));
    }

    public function deleteMessage(Request $request) {
        return response()->json($this->prepareData($request, 'deleteMessage'));
    }

    private function prepareData(Request $request, string $funcName): array {
        return [
            'class' => self::class,
            'method' => $request->getMethod(),
            'function' => $funcName,
        ];
    }
}
