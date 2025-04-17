<?php

namespace Tests\Feature\banner;

use App\Models\bannerCreator\banner;
use App\Models\bannerCreator\bannerOption;
use App\Models\User;
use Database\Factories\CreateBannerTemplateFactory;
use Database\Factories\CreateBannerViewerFactory;
use Database\Factories\CreateChannelFactory;
use Database\Factories\CreateChannelGroupFactory;
use Database\Factories\CreateServerFactory;
use Database\Factories\CreateServerGroupFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BannerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->user = User::query()->where('id', 1)->first();
    }

    public function test_get_banner_list()
    {
        CreateServerFactory::new()->create();
        CreateChannelFactory::new()->create();
        CreateChannelGroupFactory::new()->create();
        CreateServerGroupFactory::new()->create();
        User::query()->where('id', '=', 1)->update(['default_server_id'=>1]);
        $this->update_user();

        $response = $this->actingAs($this->user)->get(route('banner.view.listBanner'));
        $response->assertOk();
        $response->assertViewIs('backend.banner-creator.banner-list');
        $response->assertSeeText('No templates have been found yet');
    }

    public function test_post_upload_banner_template()
    {
        CreateServerFactory::new()->create();
        CreateChannelFactory::new()->create();
        CreateChannelGroupFactory::new()->create();
        CreateServerGroupFactory::new()->create();
        User::query()->where('id', '=', 1)->update(['default_server_id'=>1]);
        $this->update_user();

        Storage::fake('banner');
        $uploadArray = CreateBannerTemplateFactory::new()->make(['banner_original_file_name'=>UploadedFile::fake()->image('factory.png')])->toArray();

        $response = $this->actingAs($this->user)->post(route('banner.create.uploadedTemplate'), $uploadArray);
        $response->assertStatus(302);
        $response->assertSessionHas(['success'=>'Banner created successfully']);
        Storage::disk('banner')->assertExists('template/factory.png');
    }

    public function test_get_banner_config()
    {
        CreateServerFactory::new()->create();
        CreateChannelFactory::new()->create();
        CreateChannelGroupFactory::new()->create();
        CreateServerGroupFactory::new()->create();
        User::query()->where('id', '=', 1)->update(['default_server_id'=>1]);
        $this->update_user();

        //prepare
        Storage::fake('banner');
        $uploadArray = CreateBannerTemplateFactory::new()->make(['banner_original_file_name'=>UploadedFile::fake()->image('factory.png')])->toArray();
        $this->actingAs($this->user)->post(route('banner.create.uploadedTemplate'), $uploadArray);
        Storage::disk('banner')->assertExists('template/factory.png');

        $response = $this->actingAs($this->user)->get(route('banner.view.configBanner', ['id'=>1]));
        $response->assertOk();
        $response->assertViewIs('backend.banner-creator.create-banner');
    }

    public function test_post_upsert_banner_config()
    {
        CreateServerFactory::new()->create();
        CreateChannelFactory::new()->create();
        CreateChannelGroupFactory::new()->create();
        CreateServerGroupFactory::new()->create();
        User::query()->where('id', '=', 1)->update(['default_server_id'=>1]);
        $this->update_user();

        //prepare
        $uploadArray = CreateBannerTemplateFactory::new()->make(['banner_original_file_name'=>UploadedFile::fake()->image('factory.png')])->toArray();
        $this->actingAs($this->user)->post(route('banner.create.uploadedTemplate'), $uploadArray);
        Storage::disk('banner')->assertExists('template/factory.png');

        //test
        $upsertArray = CreateBannerViewerFactory::new()->make()->toArray();
        $response = $this->actingAs($this->user)->post(route('banner.upsert.configBanner', $upsertArray));
        $response->assertStatus(302);
        $response->assertSessionHas(['success'=>'Configuration successfully updated.']);

        //check
        $bannerOptions = bannerOption::query()->get();
        $banner = banner::query()->get();

        Storage::disk('banner')->assertExists('/viewer/'.$banner->first()->banner_viewer_file_name);
        $this->assertCount(1, $banner);
        $this->assertCount(1, $bannerOptions);
        $this->assertEquals('https://factory-domain.de', $banner->first()->banner_hostbanner_url);

        //cleanup - with storage faker imagepng cant find the directory path
        $deleteFiles = Storage::disk('banner')->allFiles('template/');
        foreach ($deleteFiles as $deleteFile) {
            if ($deleteFile != 'template/.gitignore') {
                Storage::disk('banner')->delete($deleteFile);
            }
        }
        $deleteFiles = Storage::disk('banner')->allFiles('viewer/');
        foreach ($deleteFiles as $deleteFile) {
            if ($deleteFile != 'viewer/.gitignore') {
                Storage::disk('banner')->delete($deleteFile);
            }
        }
    }

    public function test_post_delete_banner()
    {
        CreateServerFactory::new()->create();
        CreateChannelFactory::new()->create();
        CreateChannelGroupFactory::new()->create();
        CreateServerGroupFactory::new()->create();
        User::query()->where('id', '=', 1)->update(['default_server_id'=>1]);
        $this->update_user();

        //prepare
        $uploadArray = CreateBannerTemplateFactory::new()->make(['banner_original_file_name'=>UploadedFile::fake()->image('factory.png')])->toArray();
        $this->actingAs($this->user)->post(route('banner.create.uploadedTemplate'), $uploadArray);
        Storage::disk('banner')->assertExists('template/factory.png');
        $upsertArray = CreateBannerViewerFactory::new()->make()->toArray();
        $this->actingAs($this->user)->post(route('banner.upsert.configBanner', $upsertArray));
        $banner = banner::query()->get();

        //test
        $response = $this->actingAs($this->user)->post(route('banner.delete.banner', ['id'=>1]));
        $response->assertStatus(302);
        $response->assertSessionHas(['success'=>'Banner was successfully deleted']);

        //check
        $bannerExistResult = Storage::disk('banner')->exists('/template/'.$banner->first()->banner_original_file_name);
        $this->assertfalse($bannerExistResult);
        $bannerExistResult = Storage::disk('banner')->exists('/viewer/'.$banner->first()->banner_viewer_file_name);
        $this->assertfalse($bannerExistResult);

        $bannerOptions = bannerOption::query()->get();
        $banner = banner::query()->get();

        $this->assertCount(0, $banner);
        $this->assertCount(0, $bannerOptions);
    }

    private function update_user(): void
    {
        $this->user = User::query()->where('id', 1)->first();
    }
}
