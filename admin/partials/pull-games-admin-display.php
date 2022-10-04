<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://www.fiverr.com/junaidzx90
 * @since      1.0.0
 *
 * @package    Pull_Games
 * @subpackage Pull_Games/admin/partials
 */
?>

<h3>Pull Games</h3>
<hr>
<?php
$games_tab = true;
if(isset($_GET['page']) && $_GET['page'] === 'pull-games' && isset($_GET['tab']) && $_GET['tab'] === 'games'){
    $games_tab = true;
}
if(isset($_GET['page']) && $_GET['page'] === 'pull-games' && isset($_GET['tab']) && $_GET['tab'] === 'recommendations'){
    $games_tab = false;
}
?>
<div id="pullgames">
    <div class="_tabs">
        <a class="<?php echo (($games_tab)?'active': '') ?>" href="<?php echo admin_url( "admin.php?page=pull-games&tab=games" ) ?>">Games</a>
        <a class="<?php echo ((!$games_tab)?'active': '') ?>" href="<?php echo admin_url( "admin.php?page=pull-games&tab=recommendations" ) ?>">Recommendations</a>
    </div>
    <div class="content_wrapper">
        <div class="_tab_contents">
            <?php 
            if($games_tab){
                ?>
                <p><b class="rowsCounts">0</b> Records</p>
                <table id="resultset">
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="select-all-games"></th>
                            <th>Game name</th>
                            <th>UniverseID</th>
                            <th>Creator</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="nores">
                            <td colspan="4">No results!</td>
                        </tr>
                    </tbody>
                </table>
                <button disabled class="button-secondary dnone" id="loadMoreGames">Load more</button>
                <button disabled class="button-primary" id="import_games">Import games</button>

                <hr>

                <div class="filter_with_ids">
                    <h3>Search By UniverseID's</h3>
                    <div class="filter_input">
                        <input type="text" placeholder="Universe Id's" name="universe_ids" id="universe_ids">
                        <button class="button-secondary search_universeids">Search</button>
                    </div>
                </div>

                <div class="filter_with_keyword">
                    <h3>Search By Keyword</h3>
                    <div class="filter_input">
                        <input type="text" placeholder="Keyword" name="keyword_filter" id="keyword_filter">
                        <label for="paginate_search">Require paginate
                            <input type="checkbox" name="paginate_search" id="paginate_search">
                        </label>
                        <button class="button-secondary search_keyword">Search</button>
                    </div>
                </div>
                <?php
            }else{
                ?>
                <p><b class="rowsCounts">0</b> Records</p>
                <table id="resultset">
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="select-all-games"></th>
                            <th>Game name</th>
                            <th>UniverseID</th>
                            <th>Creator</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="nores">
                            <td colspan="4">No results!</td>
                        </tr>
                    </tbody>
                </table>
                <button disabled class="button-secondary dnone" id="loadMoreRecommendations">Load more</button>
                <button disabled class="button-primary" id="import_games">Import recommendations</button>

                <hr>

                <div class="filter_with_ids">
                    <h3>Search Experiences</h3>
                    <div class="experience_filter_box">
                        <select id="experience_filter" multiple>
                        </select>
                        <button class="button-secondary search_recommendations">Search</button>
                    </div>
                </div>
                <?php
            }
            ?>
        </div>
    </div>
</div>

<div class="pg_loader dnone">
    <svg version="1.1" id="loader-1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="50px" height="50px" viewBox="0 0 40 40" enable-background="new 0 0 40 40" xml:space="preserve">
        <path opacity="0.2" fill="#000" d="M20.201,5.169c-8.254,0-14.946,6.692-14.946,14.946c0,8.255,6.692,14.946,14.946,14.946
        s14.946-6.691,14.946-14.946C35.146,11.861,28.455,5.169,20.201,5.169z M20.201,31.749c-6.425,0-11.634-5.208-11.634-11.634
        c0-6.425,5.209-11.634,11.634-11.634c6.425,0,11.633,5.209,11.633,11.634C31.834,26.541,26.626,31.749,20.201,31.749z"></path>
        <path fill="#2271b1" d="M26.013,10.047l1.654-2.866c-2.198-1.272-4.743-2.012-7.466-2.012h0v3.312h0
        C22.32,8.481,24.301,9.057,26.013,10.047z">
        <animateTransform attributeType="xml" attributeName="transform" type="rotate" from="0 20 20" to="360 20 20" dur="0.9s" repeatCount="indefinite"></animateTransform>
        </path>
    </svg>
</div>