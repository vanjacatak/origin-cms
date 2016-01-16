<?php

namespace App\Http\Controllers;

use DB;
use Session;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class AutocompleteController extends Controller
{
	public function getAutocomplete(Request $request) {

		$status_modules = ['User'];

		$module = ucwords($request->get('module'));
		$field = $request->get('field');

		if ($request->has('fetch_fields') && $request->get('fetch_fields')) {
			$fetch_fields = $request->get('fetch_fields');
		}

		$fetch_fields = (isset($fetch_fields)) ? $fetch_fields : $field;

		$data_query = DB::table("tab".$module)->select($fetch_fields);

		// permission fields from perm controller
		if (Session::get('role') != 'Administrator') {
			$perm_fields = PermController::module_wise_permissions(Session::get('role'), 'Read', $module);

			if ($perm_fields) {
				foreach ($perm_fields as $field_name => $field_value) {
					if (is_array($field_value)) {
						$data_query = $data_query->whereIn($field_name, $field_value);
					}
					else {
						$data_query = $data_query->where($field_name, $field_value);
					}
				}
			}
		}

		// only show active data for defined tables
		if (in_array($module, $status_modules)) {
			$data = $data_query->where('status', 'Active')->get();
		}
		else {
			$data = $data_query->get();
		}

		return $data;
	}
}