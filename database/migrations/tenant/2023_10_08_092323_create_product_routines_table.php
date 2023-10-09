<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_routines', function (Blueprint $table) {
            $table->id();
            $table->integer('reseller_id')->comment('出版商ID');
            $table->string('name','255')->comment('名称');
            $table->string('thumb','255')->comment('缩列图');
            $table->integer('category_id')->comment('所属分类');
            $table->integer('country_id')->comment('所属区域');
            $table->string('keyword','255')->comment('关键词');
            $table->string('url','255')->comment('自定义链接');
            $table->integer('published_date')->comment('出版日期');
            $table->tinyInteger('status')->comment('状态:1代表有效,2代表无效。');
            $table->string('author','25')->comment('作者');
            $table->tinyInteger('recommend')->comment('是否推荐在首页显示:1代表是,2代表否,默认是2。');
            $table->tinyInteger('excellent')->comment('是否属于精品并在首页显示:1代表是,2代表否,默认是2。');
            $table->tinyInteger('have_sample')->comment('有否对应的样本文件(pdf):1代表有,2代表无。');

            $table->smallInteger('discount')->comment('折扣数值:两位整数,例如85代表85折85%。');
            $table->decimal('discount_amount')->comment('折扣金额：不打折，而是直接减去的金额，相当于优惠券，同一份报告的最终价格只能二选一（discount或discount_amount）');
            $table->integer('discount_time_begin')->comment('折扣有效期的开始时间');
            $table->integer('discount_time_end')->comment('折扣有效期的结束时间');
            $table->smallInteger('pages')->comment('页数');
            $table->smallInteger('tables')->comment('表格数Number of Tables and Figures');
            $table->integer('hits')->comment('浏览量（设置3位数的随机数）');
            $table->integer('downloads')->comment('下载数（设置3位数的随机数。仅限于下载PDF）');
            $table->decimal('price')->comment('基础价格');
            $table->integer('updated_by');
            $table->integer('created_by');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_routines');
    }
};
