<div class="uk-form uk-form-stacked" ng-class="vm.name == 'contentCtrl' ? 'uk-width-large-2-3 wk-width-xlarge-1-2' : ''" ng-controller="k2Ctrl as ctrl">

    <h3 class="wk-form-heading">{{'Content' | trans}}</h3>

    <div class="uk-form-row">
        <label class="uk-form-label" for="wk-category">{{'Category' | trans}}</label>
        <div class="uk-form-controls">
            <select id="wk-category" class="uk-width-1-1" ng-model="content.data['category']" multiple
                ng-options="cat.id as cat.name for cat in k2.categories">
            </select>
        </div>
    </div>

    <div class="uk-form-row">
        <div class="uk-form-controls">
            <label class="uk-form-label" for="wk-k2-subcat">{{'Include Subcategories' | trans}}</label>
            <div class="uk-form-controls">
                <select id="wk-k2-subcat" class="uk-width-1-1" ng-model="content.data.subcategories">
                    <option value="0">{{'No' | trans}}</option>
                    <option value="1">{{'Yes' | trans}}</option>
                </select>
            </div>
        </div>
    </div>

    <div class="uk-form-row">
        <label class="uk-form-label" for="wk-number">{{'Limit' | trans}}</label>
        <div class="uk-form-controls">
            <input id="wk-number" class="uk-width-1-1" type="number" value="5" min="1" step="1" ng-model="content.data['number']">
        </div>
    </div>

    <div class="uk-form-row">
        <label class="uk-form-label" for="wk-order">{{'Order' | trans}}</label>
        <div class="uk-form-controls">
            <select id="wk-order" class="uk-width-1-1" ng-model="content.data['ordering']">
                <option value="">{{'Default' | trans}}</option>
                <option value="rdate">{{'Latest First' | trans}}</option>
                <option value="date">{{'Latest Last' | trans}}</option>
                <option value="alpha">{{'Alphabetical' | trans}}</option>
                <option value="ralpha">{{'Alphabetical Reversed' | trans}}</option>
                <option value="hits">{{'Most Hits' | trans}}</option>
                <option value="publishUp">{{'Recent' | trans}}</option>
                <option value="order">{{'Ordering' | trans}}</option>
                <option value="rorder">{{'Ordering Reversed' | trans}}</option>
                <option value="best">{{'Highest Rated' | trans}}</option>
                <option value="comments">{{'Most Commented' | trans}}</option>
                <option value="modified">{{'Latest Modified' | trans}}</option>
                <option value="rand">{{'Random' | trans}}</option>
            </select>
        </div>
    </div>

    <h3 class="wk-form-heading uk-margin-large-top">{{'Mapping' | trans}}</h3>

    <ul ng-if="(k2.fields | toArray).length > 1" class="uk-tab" data-uk-tab="{connect:'#k2-mapping'}">
        <li ng-class="{'uk-active' : $first}" ng-repeat="(name, group) in k2.fields"><a href="">{{name}}</a></li>
    </ul>

    <ul id="k2-mapping" class="uk-switcher uk-margin">

        <li ng-class="{'uk-active' : $first}" ng-repeat="(group, fields) in k2.fields">

            <div ng-repeat="field in content.data.fields" class="uk-grid uk-grid-small uk-grid-width-1-2">
                <div>
                    <label ng-if="$first" class="uk-form-label">{{'Widgetkit Field' | trans}}</label>
                    <div class="uk-form-controls">
                        <input type="text" class="uk-width-1-1" value="{{field.name}}" disabled>
                    </div>
                </div>
                <div>
                    <label ng-if="$first" class="uk-form-label">{{'K2 Field' | trans}}</label>
                    <div class="uk-flex uk-flex-middle">
                        <select data-id="{{field.id}}" class="uk-width-1-1" ng-model="content.data.mapping[group][field.id]"
                            ng-options="f.id as f.name group by f.type for f in k2.fields[group]">
                        </select>
                        <a ng-if="!field.core" class="uk-margin-left uk-text-danger" ng-click="ctrl.deleteField(field)"><i class="uk-icon-trash-o"></i></a>
                    </div>
                </div>
            </div>

        </li>

    </ul>

    <p>
        <input id="k2-field-new" type="text" placeholder="{{'Field' | trans}}">
        <a class="uk-button" ng-click="ctrl.addField()">{{'Add' | trans}}</a>
    </p>

</div>

<script type="k2/config">
    <?php echo json_encode($app['plugins']['content/k2']->getFormData()); ?>
</script>
