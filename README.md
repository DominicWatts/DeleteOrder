# Magento 2 Delete Order # 

![phpcs](https://github.com/DominicWatts/DeleteOrder/workflows/phpcs/badge.svg)

![PHPCompatibility](https://github.com/DominicWatts/DeleteOrder/workflows/PHPCompatibility/badge.svg)

![PHPStan](https://github.com/DominicWatts/DeleteOrder/workflows/PHPStan/badge.svg)

Delete order via admin or in console script.  Database level delete covering many tables.

# Install instructions #

`composer require dominicwatts/deleteorder`

`php bin/magento setup:upgrade`

# Usage Instructions #

Console command to delete orders

`xigen:deleteorder:delete [-o|--orderid [ORDERID]] [-i|--increment [INCREMENT]] [--] [<all>]`

`php bin/magento xigen:deleteorder:delete -o 25`

`php bin/magento xigen:deleteorder:delete -i 000000024`

`php bin/magento xigen:deleteorder:delete all`

Additional warning in place to catch [all] delete

```
php bin/magento xigen:deleteorder:delete all
2019-07-24 22:14:25 Start Processing orders
You are about to remove all your orders. Are you sure?[y/N]
```

Admin screen

![Admin delete button](https://i.snag.gy/Rx7hUw.jpg)

Admin order grid mass action

![Admin delete button](https://i.snag.gy/vC52zx.jpg)