<?php

namespace App\Console\Commands;

use App\Models\Character\Character;
use App\Models\Feature\Feature;
use App\Models\Item\Item;
use App\Models\Prompt\Prompt;
use App\Models\Shop\Shop;
use App\Models\SiteIndex;
use App\Models\SitePage;
use App\Models\User\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class IndexSitePages extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'index-new-search-pages';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Indexes all site content for the ajax search.';

    /**
     * Create a new command instance.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle() {
        if (Schema::hasTable('site_temp_index')) {
            //A. ------------------ Clear the temp table for extra insurance
            DB::table('site_temp_index')->truncate();

            //B. ------------------ Index types of content
            //1. FIND ALL CHARACTERS TO INDEX
            $characters = Character::visible()->myo(0)->get();
            foreach ($characters as $character) {
                DB::table('site_temp_index')->insert([
                    // input all neccessary fields
                    'id'          => $character->id,
                    'title'       => $character->slug.': '.$character->name,
                    'type'        => get_class($character),
                    'identifier'  => $character->slug,
                    'description' => $character->name,
                ]);
            }

            //2. FIND ALL PAGES TO INDEX
            $pages = SitePage::where('is_visible', 1)->get();
            foreach ($pages as $page) {
                DB::table('site_temp_index')->insert([
                    // input all neccessary fields
                    'id'          => $page->id,
                    'title'       => $page->title,
                    'type'        => get_class($page),
                    'identifier'  => $page->key,
                    'description' => substr_replace(strip_tags($page->parsed_text), '...', 100),
                ]);
            }

            //3. FIND ALL USERS TO INDEX
            $users = User::all();
            foreach ($users as $user) {
                DB::table('site_temp_index')->insert([
                    // input all neccessary fields
                    'id'          => $user->id,
                    'title'       => $user->name,
                    'type'        => get_class($user),
                    'identifier'  => $user->name,
                    'description' => null,
                ]);
            }

            //4. FIND ALL ITEMS TO INDEX
            $items = Item::all();
            foreach ($items as $item) {
                DB::table('site_temp_index')->insert([
                    // input all neccessary fields
                    'id'          => $item->id,
                    'title'       => $item->name,
                    'type'        => get_class($item),
                    'identifier'  => $item->name,
                    'description' => substr_replace(strip_tags($item->parsed_description), '...', 100),
                ]);
            }

            //5. FIND ALL PROMPTS TO INDEX
            $prompts = Prompt::active()->get();
            foreach ($prompts as $prompt) {
                DB::table('site_temp_index')->insert([
                    // input all neccessary fields
                    'id'          => $prompt->id,
                    'title'       => $prompt->name,
                    'type'        => get_class($prompt),
                    'identifier'  => $prompt->id,
                    'description' => substr_replace(strip_tags($prompt->parsed_description), '...', 100),
                ]);
            }

            //6. FIND ALL SHOPS TO INDEX
            $shops = Shop::where('is_active', 1)->get();
            foreach ($shops as $shop) {
                DB::table('site_temp_index')->insert([
                    // input all neccessary fields
                    'id'          => $shop->id,
                    'title'       => $shop->name,
                    'type'        => get_class($shop),
                    'identifier'  => $shop->id,
                    'description' => substr_replace(strip_tags($shop->parsed_description), '...', 100),
                ]);
            }

            //7. FIND ALL TRAITS TO INDEX
            $features = Feature::visible()->get();
            foreach ($features as $feature) {
                DB::table('site_temp_index')->insert([
                    // input all neccessary fields
                    'id'          => $feature->id,
                    'title'       => $feature->name,
                    'type'        => get_class($feature),
                    'identifier'  => $feature->name,
                    'description' => substr_replace(strip_tags($feature->parsed_description), '...', 100),
                ]);
            }

            /* IMPORTANT
            * If you would like to add your own areas to search you can easily add them here! Just copy one of the sections above and replace the data as needed.
            * note: identifier field should always match the URL parameter. (Characters use slug, pages use key, etc)
            * Ensure if you use content for the description it does NOT go over 1024 characters.
            * ID is not an incremental field.
            */

            // ------------------ C. Duplicate data to new table
            DB::table('site_index')->truncate();
            $index = DB::table('site_temp_index')->get();
            foreach ($index as $row) {
                SiteIndex::create([
                    // input all neccessary fields
                    'id'          => $row->id,
                    'title'       => $row->title,
                    'type'        => $row->type,
                    'identifier'  => $row->identifier,
                    'description' => $row->description,
                ]);
            }

            // ------------------ D. Dump the Temp Table
            DB::table('site_temp_index')->truncate();
        }
    }
}
