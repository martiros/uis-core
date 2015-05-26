<?php
namespace UIS\Core\View;

use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        \Blade::directive('titleKey', function($expression) {
            return '<?php echo \UIS\Core\Page\PageFacade::title(\Illuminate\Support\Facades\Lang::trans(' . $expression . ')); ?>';
        });

        \Blade::directive('title', function($expression) {
            if (empty($expression)) {
                return '<?php echo \UIS\Core\Page\PageFacade::title(); ?>';
            } else {
                return '<?php echo \UIS\Core\Page\PageFacade::title(\Illuminate\Support\Facades\Lang::trans(' . $expression . ')); ?>';
            }
        });
    }

}
