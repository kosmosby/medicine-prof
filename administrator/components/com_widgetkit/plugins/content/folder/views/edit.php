<div class="uk-form uk-form-stacked" ng-controller="folderCtrl as folder">
    <h3 class="wk-form-heading">{{'Image Folder' | trans}}</h3>

    <div class="uk-form-row">
        <label class="uk-form-label">Folder path</label>
        <div class="uk-form-controls">
            <input type="text" ng-model="content.data['folder']" class="uk-width-1-1">
        </div>
    </div>

    <h3 class="wk-form-heading">{{'Options' | trans}}</h3>

    <div class="uk-form-row">
        <label class="uk-form-label">{{'Sort by' | trans}}</label>
        <div class="uk-form-controls">
            <select class="uk-width-1-1" ng-model="content.data['sort_by']">
                <option value="filename_asc">{{'Alphabetical' | trans}}</option>
                <option value="filename_desc">{{'Alphabetical Reversed' | trans}}</option>
                <option value="date_desc">{{'Latest First' | trans}}</option>
                <option value="date_asc">{{'Latest Last' | trans}}</option>
                <option value="random">{{'Random' | trans}}</option>
            </select>
        </div>
    </div>

    <div class="uk-form-row">
        <label><input type="checkbox" ng-model="content.data['strip_leading_numbers']"> {{'Remove leading numbers from title' | trans}}</label>
    </div>

    <div class="uk-form-row">
        <label><input type="checkbox" ng-model="content.data['strip_trailing_numbers']"> {{'Remove trailing numbers from title' | trans}}</label>
    </div>

</div>