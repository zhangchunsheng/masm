masm
====

make a snow man

nginx rewrite

location /
{
    try_files $uri $uri/ /index.php?q=$uri&$args;
}