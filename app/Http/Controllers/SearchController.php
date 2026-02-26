<?php

namespace App\Http\Controllers;

use App\Models\SiteIndex;
use Illuminate\Http\Request;

class SearchController extends Controller {
    public function siteSearch(Request $request) {
        $input = $request->input('s');

        $result = SiteIndex::where('title', 'like', '%'.$input.'%')
            ->orWhere('description', 'like', '%'.$input.'%')
            ->limit(25)
            ->get();

        foreach ($result as $r) {
            $row = '<div class="resultrow"><a href="'.$r->indexedModel->url.'"><div class="title"><span class="badge badge-secondary">'.$r->typeLabel.'</span>'.$r->title.'</div></a></div>';
            echo $row;
        }
    }
}
