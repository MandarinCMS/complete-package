<?php
/**
 * GardenLogin Import Export Page Content.
 * @package GardenLogin
 * @since 1.0.19
 */
?>
<div class="gardenlogin-import-export-page">
  <h2><?php esc_html_e( 'Import/Export GardenLogin Settings', 'gardenlogin' ); ?></h2>
  <div class=""><?php esc_html_e( "Import/Export your GardenLogin Settings for/from other sites. This will export/import all the settings including Customizer settings as well.", 'gardenlogin' ); ?></div>
  <table class="form-table">
    <tbody>
    <tr class="import_setting">
        <th scope="row">
          <label for="gardenlogin_configure[import_setting]"><?php esc_html_e( 'Import Settings:', 'gardenlogin' ); ?></label>
        </th>
        <td>
          <input type="file" name="loginPressImport" id="loginPressImport">
          <input type="button" class="button gardenlogin-import" value="<?php esc_html_e( 'Import', 'gardenlogin' ); ?>" multiple="multiple" disabled="disabled">
          <span class="import-sniper">
            <img src="<?php echo admin_url( 'images/mcmsspin_light.gif' ); ?>">
          </span>
          <span class="import-text"><?php esc_html_e( 'GardenLogin Settings Imported Successfully.', 'gardenlogin' ); ?></span>
          <span class="wrong-import"></span>
          <p class="description"><?php esc_html_e( 'Select a file and click on Import to start processing.', 'gardenlogin' ); ?></p>
        </td>
      </tr>
      <tr class="export_setting">
        <th scope="row">
          <label for="gardenlogin_configure[export_setting]"><?php esc_html_e( 'Export Settings:', 'gardenlogin' ); ?></label>
        </th>
        <td>
          <input type="button" class="button gardenlogin-export" value="<?php esc_html_e( 'Export', 'gardenlogin' ); ?>">
          <span class="export-sniper">
            <img src="<?php echo admin_url( 'images/mcmsspin_light.gif' ); ?>">
          </span>
          <span class="export-text"><?php esc_html_e( 'GardenLogin Settings Exported Successfully!', 'gardenlogin' ); ?></span>
          <p class="description"><?php esc_html_e( 'Export GardenLogin Settings.', 'gardenlogin' ) ?></p>
        </td>
      </tr>
    </tbody>
  </table>
</div>
