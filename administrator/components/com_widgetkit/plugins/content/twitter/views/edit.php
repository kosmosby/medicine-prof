<div class="uk-form uk-form-horizontal"
     ng-class="vm.name == 'contentCtrl' ? 'uk-width-large-2-3 wk-width-xlarge-1-2' : ''"
     data-status="<?php echo $app['option']->has('twitter_token') ?>"
     ng-controller="twitterCtrl as twitter">

    <h3 class="wk-form-heading">{{'Content' | trans}}</h3>

    <div class="uk-form-row">
        <label class="uk-form-label" for="wk-source">{{'Source' | trans}}</label>

        <div class="uk-form-controls">
            <select id="wk-source" class="uk-form-width-medium" name="source" ng-model="content.data['source']">
                <option value="user">{{'User' | trans}}</option>
                <option value="search">{{'Search' | trans}}</option>
            </select>

            <input type="text" placeholder="{{ 'Username' | trans}}" ng-model="content.data['search']" ng-if="content.data['source'] == 'user'">

            <input type="text" placeholder="{{ 'Query' | trans}}" ng-model="content.data['search']" ng-if="content.data['source'] == 'search'">

            <p class="uk-text-muted" ng-if="content.data['source'] == 'search'">{{'Displays tweets matching the search. Use any string, a #hashtag or @username to find tweets mentioning the user.' | trans}}</p>
            <p class="uk-text-muted" ng-if="content.data['source'] == 'user'">{{'Finds all tweets from a single user.' | trans}}</p>
        </div>
    </div>

    <h3 class="wk-form-heading">Filter</h3>

    <div class="uk-form-row">
        <label class="uk-form-label" for="wk-limit">{{'Limit' | trans}}</label>

        <div class="uk-form-controls">
            <input id="wk-limit" min="1" class="uk-form-width-medium" type="number" ng-model="content.data['limit']">
        </div>
    </div>

    <div class="uk-form-row">
        <span class="uk-form-label" for="wk-include-rts">{{'Retweets' | trans}}</span>

        <div class="uk-form-controls uk-form-controls-text">
            <label><input id="wk-include-rts" type="checkbox" ng-model="content.data['include_rts']"> Include retweets
                in the results</label>
        </div>
    </div>

    <div class="uk-form-row">
        <span class="uk-form-label" for="wk-media">{{'Media' | trans}}</span>

        <div class="uk-form-controls uk-form-controls-text">
            <label><input id="wk-media" type="checkbox" ng-model="content.data['only_media']"> Only include tweets which
                have media attached</label>
        </div>
    </div>

    <div class="uk-form-row" ng-if="content.data['source'] == 'user'">
        <span class="uk-form-label" for="wk-include-replies">{{'Replies' | trans}}</span>

        <div class="uk-form-controls uk-form-controls-text">
            <label><input id="wk-include-replies" type="checkbox" ng-model="content.data['include_replies']"> Include
                replies</label>
        </div>
    </div>

    <h3 class="wk-form-heading">{{'Mapping' | trans}}</h3>

    <div class="uk-grid uk-grid-small uk-grid-width-1-2">
        <div>

            <label class="uk-form-label">{{'Widgetkit Field' | trans}}</label>

            <div class="uk-form-controls">
                <input type="text" class="uk-width-1-1" value="title" disabled>
            </div>

        </div>
        <div>

            <label class="uk-form-label">{{'Tweet Property' | trans}}</label>

            <div class="uk-form-controls">
                <select class="uk-width-1-1" ng-model="content.data['title']">
                    <option value="name">{{'Name' | trans}}</option>
                    <option value="screen_name">{{'Screen name' | trans}}</option>
                    <option value="combined">{{'Name and screen name' | trans}}</option>
                </select>
            </div>

        </div>
    </div>

    <h3 class="wk-form-heading">{{'Twitter API' | trans}}</h3>

    <div class="uk-alert uk-alert-danger" ng-if="content.data['error']">
        <span class="uk-text-bold">{{'Twitter API response:' | trans}}</span> {{ content.data['error'] }}
    </div>
    <input id="wk-error" type="hidden" ng-model="content.data['error']">

    <div class="uk-form-row">
        <label class="uk-form-label" for="wk-twitter-pin">{{'Authorization' | trans}}</label>

        <div class="uk-form-controls">

            <input id="wk-twitter-pin" type="text" placeholder="{{'PIN' | trans}}" ng-model="twitter.pin"
                   ng-model-options="{ debounce: 300 }" ng-if="!twitter.connected">

            <a class="uk-button" ng-click="twitter.openPopup('<?php echo $app['url']->route('twitter_auth') ?>')"
               ng-if="!twitter.connected && !twitter.loading">{{'Request PIN' | trans}}</a>

            <a class="uk-button" ng-click="twitter.disconnect()" ng-if="twitter.connected">{{'Disconnect' | trans}}</a>

            <i class="uk-icon-medium uk-icon-spinner uk-icon-spin" ng-if="twitter.loading"></i>

            <p class="uk-text-muted" ng-if="!twitter.connected">
                <span class="uk-badge uk-badge-danger uk-text-bold">Not configured</span> {{'To connect with Twitter, click the button above. Follow the instructions and copy the provided PIN.' | trans}}
            </p>

            <p class="uk-text-muted" ng-if="twitter.connected">{{'Disconnecting from Twitter will affect all widgets.' | trans}}</p>

        </div>
    </div>
</div>