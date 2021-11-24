<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use MicroweberPackages\Post\Models\Post;
use MicroweberPackages\Product\Models\Product;
use Tests\Browser\Components\AdminContentCategorySelect;
use Tests\Browser\Components\AdminContentCustomFieldAdd;
use Tests\Browser\Components\AdminContentImageAdd;
use Tests\Browser\Components\AdminContentTagAdd;
use Tests\Browser\Components\AdminLogin;
use Tests\DuskTestCase;

class AdminAddProductTest extends DuskTestCase
{
    public function testAddProduct()
    {
        $this->browse(function (Browser $browser) {

            $browser->within(new AdminLogin, function ($browser) {
                $browser->fillForm();
            });

            $productPrice = rand(1111,9999);
            $productSpecialPrice = $productPrice - rand(1,9);
            $productTitle = 'This is the product title'.time();
            $productDescription = 'This is the product description'.time();

            $browser->visit(route('admin.product.create'));

            $browser->pause(3000);
            $browser->value('#slug-field-holder input', $productTitle);

            $browser->pause(3000);
            $browser->keys('.mw-editor-area', $productDescription);
            $browser->pause(1000);

            $browser->value('.js-product-price', $productPrice);
            $browser->pause(1000);

            $browser->click('.js-toggle-offer-price-button');
            $browser->pause(2000);
            $browser->value('.js-product-special-price', $productSpecialPrice);
            $browser->pause(1000);


            $category4 = 'Shop';
            $category4_1 = 'Clothes';
            $category4_2 = 'T-shirts';
            $category4_3 = 'Decor';

            $browser->within(new AdminContentCategorySelect, function ($browser) use($category4,$category4_1,$category4_2,$category4_3) {
                $browser->selectCategory($category4);
                $browser->selectSubCategory($category4,$category4_1);
                $browser->selectSubCategory($category4,$category4_2);
                $browser->selectSubCategory($category4,$category4_3);
            });

            $tag1 = 'Tagdusk-'.time().rand(100,200);
            $tag2 = 'Tagdusk-'.time().rand(200,300);
            $tag3 = 'Tagdusk-'.time().rand(300,400);
            $tag4 = 'Tagdusk-'.time().rand(400,500);

            $browser->within(new AdminContentTagAdd, function ($browser) use($tag1, $tag2, $tag3, $tag4) {
                $browser->addTag($tag1);
                $browser->addTag($tag2);
                $browser->addTag($tag3);
                $browser->addTag($tag4);
            });

            // add images to gallery
            $browser->within(new AdminContentImageAdd, function ($browser) {
                $browser->addImage(userfiles_path() . '/templates/default/img/patterns/img1.jpg');
                $browser->addImage(userfiles_path() . '/templates/default/img/patterns/img2.jpg');
                $browser->addImage(userfiles_path() . '/templates/default/img/patterns/img3.jpg');
            });


            $browser->within(new AdminContentCustomFieldAdd, function ($browser) {
                $browser->addCustomField('dropdown','Dropdown');
                $browser->addCustomField('text','Text Field');
            });

            $browser->pause(1000);
            $browser->click('#js-admin-save-content-main-btn');
            $browser->pause(10000);

            $findProduct = Product::where('title', $productTitle)->first();

            $this->assertEquals($findProduct->content, $productDescription);
            $this->assertEquals($findProduct->content_type, 'product');
            $this->assertEquals($findProduct->subtype, 'product');

            $tags = content_tags($findProduct->id);
            $this->assertTrue(in_array($tag1,$tags));
            $this->assertTrue(in_array($tag2,$tags));
            $this->assertTrue(in_array($tag3,$tags));
            $this->assertTrue(in_array($tag4,$tags));

            $findedCategories = [];
            $categories = content_categories($findProduct->id);
            foreach ($categories as $category) {
                $findedCategories[] = $category['title'];
            }

            $this->assertTrue(in_array('Decor',$findedCategories));
            $this->assertTrue(in_array('Clothes',$findedCategories));
            $this->assertTrue(in_array('T-shirts',$findedCategories));

            $findedCustomFields = [];
            $customFields = content_custom_fields($findProduct->id);
            foreach ($customFields as $customField) {
                $findedCustomFields[] = $customField['name'];
            }
            $this->assertTrue(in_array('Dropdown',$findedCustomFields));
            $this->assertTrue(in_array('Text Field',$findedCustomFields));

            $description = content_description($findProduct->id);
            $this->assertEquals($description, $productDescription);

            $getPictures = get_pictures($findProduct->id);
            $this->assertEquals(3, count($getPictures));

            $browser->waitForLocation(route('admin.product.edit', $findProduct->id));

        });

    }
}