INSERT INTO `admin_role_auth`(`pid`, `auth_name`, `method`, `path`, `status`, `sort`)
VALUES (33, '标记争议', 'POST', '/orders/collection/status', 1, 1);
INSERT INTO `admin_role_auth`(`pid`, `auth_name`, `method`, `path`, `status`, `sort`)
VALUES (33, '上传凭证', 'PUT', '/orders/collection/status', 1, 1);


INSERT INTO `admin_role_auth`(`pid`, `auth_name`, `method`, `path`, `status`, `sort`)
VALUES (32, '驳回', 'POST', '/orders/pay/reject', 1, 1);
INSERT INTO `admin_role_auth`(`pid`, `auth_name`, `method`, `path`, `status`, `sort`)
VALUES (49, '代付', 'POST', '/orders/pay/pay', 1, 1);
