<?php

namespace App\Http\Controllers\tsConfig;

use App\Http\Controllers\Controller;
use App\Http\Requests\tsConfig\CreateNewBadNameRequest;
use App\Http\Requests\tsConfig\DeleteBadNameRequest;
use App\Models\sys\badName;
use App\Models\tsBot\tsBotLog;
use App\Models\tsBotWorkers\tsBotWorkerPolice;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class BadNameController extends Controller
{
    public function viewListBadNames(): View|Factory|Application
    {
        $badNames = badName::query()
            ->where('server_id', '=', Auth::user()->active_server_id)
            ->get();

        $globalBadNames = badName::query()
            ->where('server_id', '=', 0)
            ->get();

        return view('backend.jobs.worker.bad-names.worker-bad-names')->with([
            'badNames'=>$badNames,
            'globalBadNames'=>$globalBadNames,
        ]);
    }

    public function createNewBadName(CreateNewBadNameRequest $request): RedirectResponse
    {
        badName::query()->create([
            'server_id'=>$request->validated('server_id'),
            'description'=>$request->validated('description'),
            'value_option'=>$request->validated('value_option'),
            'value'=>strtolower($request->validated('value')),
        ]);

        return redirect()->route('worker.view.badNames');
    }

    public function deleteBadName(DeleteBadNameRequest $request): RedirectResponse
    {
        $this->deleteBadNameEntryByID($request->validated('server_id'), $request->input('id'));

        return redirect()->route('worker.view.badNames');
    }

    public function checkBadName(string $proofName, int $server_id): bool
    {
        $is_globalListActive = tsBotWorkerPolice::query()
            ->where('server_id', '=', $server_id)
            ->first('is_bad_name_protection_global_list_active')->is_bad_name_protection_global_list_active;

        if ($is_globalListActive == true) {
            $checkNames = badName::query()
                ->where(function ($query) use ($server_id) {
                    $query->where('server_id', '=', $server_id)
                        ->orWhere('server_id', '=', 0);
                })
                ->where('value_option', '=', badName::stringRegex)
                ->where('is_failed', '=', false)
                ->get(['value', 'id']);
        } else {
            $checkNames = badName::query()
                ->where('server_id', '=', $server_id)
                ->where('value_option', '=', badName::stringRegex)
                ->where('is_failed', '=', false)
                ->get(['value', 'id']);
        }

        foreach ($checkNames as $checkName) {
            try {
                $badNameResultRegex = preg_match($checkName->value, $proofName);
            } catch (Exception) {
                tsBotLog::query()->create([
                    'server_id'=>$server_id,
                    'status_id'=>4,
                    'job'=>'checkBadName',
                    'description'=>'Bad Name Protection (Regex)',
                    'error_message'=>'Regex failed: '.$checkName->value,
                    'worker'=>'PoliceWorker',
                ]);

                badName::query()->where('id', '=', $checkName->id)
                    ->update([
                        'is_failed'=>true,
                    ]);
            }

            if ($badNameResultRegex == 1) {
                return true;
            }
        }

        //check contains
        $checkNames = badName::query()
            ->where('server_id', '=', $server_id)
            ->where('value_option', '=', badName::stringContains)
            ->get('value');

        foreach ($checkNames as $checkName) {
            $badNameResultContains = Str::contains($proofName, $checkName->value, true);

            if ($badNameResultContains == true) {
                return true;
            }
        }

        return false;
    }

    public function deleteBadNameEntryByID(int $server_id, int $id): void
    {
        badName::query()
            ->where('server_id', '=', $server_id)
            ->where('id', '=', $id)
            ->delete();
    }

    public function deleteBadNameEntrysByServerID(int $server_id): void
    {
        badName::query()->where('server_id', '=', $server_id)->delete();
    }

}
