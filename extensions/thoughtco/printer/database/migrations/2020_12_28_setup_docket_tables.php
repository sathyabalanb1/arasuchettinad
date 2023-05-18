<?php

namespace Thoughtco\Printer\Database\Migrations;

use File;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Thoughtco\Printer\Models\Docket;
use Thoughtco\Printer\Models\Printer;
use Thoughtco\Printer\Models\Settings;

class DocketTables extends Migration
{
    public function up()
    {
        Schema::create('thoughtco_printer_dockets', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->text('label');
            $table->mediumText('docket_settings');
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();
        });

        Schema::create('thoughtco_printer_has_dockets', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('printer_docket_id');
            $table->integer('printer_id');
            $table->integer('docket_id');
            $table->integer('priority');
            $table->mediumText('settings');
        });

        $this->addDefaultDockets();
        $this->migrateExistingDockets();
    }

    public function down()
    {
        Schema::dropIfExists('thoughtco_printer_dockets');
        Schema::dropIfExists('thoughtco_printer_has_dockets');
    }

    protected function addDefaultDockets()
    {
        Docket::create([
            'label' => 'Kitchen Receipt',
            'docket_settings' => [
                'lines_before' => 0,
                'lines_after' => 0,
                'format' => $this->getFileContent('dockets/kitchen')
            ]
        ]);

        Docket::create([
            'label' => 'Customer Receipt',
            'docket_settings' => [
                'lines_before' => 0,
                'lines_after' => 0,
                'format' => $this->getFileContent('dockets/customer')
            ]
        ]);

        Docket::create([
            'label' => 'Restaurant Receipt',
            'docket_settings' => [
                'lines_before' => 0,
                'lines_after' => 0,
                'format' => $this->getFileContent('dockets/restaurant')
            ]
        ]);
    }

    protected function migrateExistingDockets()
    {
        Printer::all()
        ->each(function($printer) {

            if ($printer->printer_settings['usedefault']) {
				$linesBefore = Settings::get('lines_before', 0);
                $linesAfter = Settings::get('lines_after', 0);
                $format = Settings::get('output_format', '');
            } else {
 				$linesBefore = $printer->printer_settings['lines_before'] ?? 0;
                $linesAfter = $printer->printer_settings['lines_after'] ?? 0;
                $format = $printer->printer_settings['format'] ?? '';
            }

            if ($format != '') {

                $docket = Docket::create([
                    'label' => $printer->label.' [migrated]',
                    'docket_settings' => [
                        'lines_before' => $linesBefore,
                        'lines_after' => $linesAfter,
                        'format' => $format,
                    ]
                ]);

                $printer->dockets()->create([
                    'docket_id' => $docket->id,
                    'settings' => [
                        'copies' => 1,
                        'categories' => $printer->printer_settings['categories'] ?? [],
                        'contexts' => '0',
                    ]
                ]);
            }

        });
    }

    protected function getFileContent(string $filePath): string
    {
		$filePath = extension_path('thoughtco/printer/').$filePath;

        if (File::exists($path = $filePath.'.blade.php'))
            return File::get($path);

        return File::get($filePath.'.php');
    }
}
