<?php

namespace Rydeen\Dealer\DataGrids;

use Webkul\B2BSuite\DataGrids\Admin\CompanyDataGrid;

class RydeenCompanyDataGrid extends CompanyDataGrid
{
    /**
     * Prepare actions — inherit edit/delete from parent, add resend invitation.
     */
    public function prepareActions()
    {
        parent::prepareActions();

        if (bouncer()->hasPermission('customer.companies.edit')) {
            $this->addAction([
                'index'  => 'resend-invitation',
                'icon'   => 'icon-mail',
                'title'  => trans('rydeen-dealer::app.admin.resend-invitation'),
                'method' => 'POST',
                'url'    => function ($row) {
                    return route('admin.rydeen.dealers.resend-invitation', $row->customer_id);
                },
            ]);
        }
    }
}
