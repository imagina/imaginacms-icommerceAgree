<?php

namespace Modules\Icommerceagree\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Icommerce\Entities\ShippingMethod;

class IcommerceagreeDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($methodsFromOther=null)
    {
        Model::unguard();

        $this->call(IcommerceagreeModuleTableSeeder::class);
        $methods = config('asgard.icommerceagree.config.methods');

        //Only the methods that match with the config
        if(!is_null($methodsFromOther))
            $methods = array_intersect_key($methods,$methodsFromOther);

        if(count($methods)>0){

            $init = "Modules\Icommerceagree\Http\Controllers\Api\IcommerceAgreeApiController";

            foreach ($methods as $key => $method) {

                $result = ShippingMethod::where('name',$method['name'])->first();

                if(!$result){

                    $options['init'] = $init;

                    $titleTrans = $method['title'];
                    $descriptionTrans = $method['description'];

                    $params = array(
                        'name' => $method['name'],
                        'status' => $method['status'],
                        'options' => $options
                    );

                    if(isset($method['parent_name']))
                        $params['parent_name'] = $method['parent_name'];

                    $shippingMethod = ShippingMethod::create($params);

                    $this->addTranslation($shippingMethod,'en',$titleTrans,$descriptionTrans);
                    $this->addTranslation($shippingMethod,'es',$titleTrans,$descriptionTrans);

                }else{
                     $this->command->alert("This method: {$method['name']} has already been installed !!");
                }
            }

        }else{
           $this->command->alert("No methods in the Config File !!"); 
        }
 
        
    }

    /*
    * Add Translations
    * PD: New Alternative method due to problems with astronomic translatable
    **/
    public function addTranslation($shippingMethod,$locale,$title,$description){

      \DB::table('icommerce__shipping_method_translations')->insert([
          'title' => trans($title,[],$locale),
          'description' => trans($description,[],$locale),
          'shipping_method_id' => $shippingMethod->id,
          'locale' => $locale
      ]);

    }

}
