<?php

use Rydeen\Dealer\DataGrids\RydeenCompanyDataGrid;
use Webkul\B2BSuite\DataGrids\Admin\CompanyDataGrid;

it('is bound in the container as the CompanyDataGrid', function () {
    $instance = app(CompanyDataGrid::class);
    expect($instance)->toBeInstanceOf(RydeenCompanyDataGrid::class);
});
