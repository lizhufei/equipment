### 各路厂商 门禁和抓抓拍摄像头 API 对接
- 发布数据迁移后,把数据填充文件复制到项目`database/seeders`下并执行
  `php artisan db:seed --class=UserSeeder` and
  `php artisan db:seed --class=DeviceSeeder`
- 发布配置 `php artisan vendor:publish --provider="Hs\Equipment\EquipmentServiceProvider"`
