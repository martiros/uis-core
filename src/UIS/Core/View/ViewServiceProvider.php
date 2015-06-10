<?php

namespace UIS\Core\View;

use Illuminate\Support\ServiceProvider;
use Blade;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function boot()
    {
        Blade::directive(
            'titleKey',
            function ($expression) {
                return '<?php echo \UIS\Core\Page\PageFacade::title(\Illuminate\Support\Facades\Lang::trans('.$expression.')); ?>';
            }
        );

        Blade::directive(
            'title',
            function ($expression) {
                if (empty($expression)) {
                    return '<?php echo \UIS\Core\Page\PageFacade::title(); ?>';
                } else {
                    return '<?php echo \UIS\Core\Page\PageFacade::title(\Illuminate\Support\Facades\Lang::trans('.$expression.')); ?>';
                }
            }
        );

        Blade::directive(
            'titleTemplate',
            function ($expression) {
                return '<?php echo \UIS\Core\Page\PageFacade::setTitleTemplate('.$expression.'); ?>';
            }
        );

        Blade::directive(
            'titleTemplateKey',
            function ($expression) {
                return '<?php echo \UIS\Core\Page\PageFacade::setTitleTemplate(\Illuminate\Support\Facades\Lang::trans('.$expression.')); ?>';
            }
        );

        Blade::directive(
            'disableTitleTemplate',
            function () {
                return '<?php echo \UIS\Core\Page\PageFacade::disableTitleTemplate(); ?>';
            }
        );

        Blade::directive(
            'enableTitleTemplate',
            function () {
                return '<?php echo \UIS\Core\Page\PageFacade::enableTitleTemplate(); ?>';
            }
        );
    }

    public function register()
    {
    }
}
