#include <stdio.h>
#include <string.h>
#include <stdlib.h>
int main (){
int i = 5;
if (i <= 0){return 0;}i--;char buff[2000];sprintf(buff, "Sully_%d.c", i);FILE *f = fopen(buff, "w");char *a = "#include <stdio.h>%1$c#include <string.h>%1$c#include <stdlib.h>%1$cint main (){%1$cint i = %4$d;%1$cif (i <= 0){return 0;}i--;char buff[2000];sprintf(buff, %2$cSully_%%d.c%2$c, i);FILE *f = fopen(buff, %2$cw%2$c);char *a = %2$c%3$s%2$c;fprintf(f, a, 10, 34, a, i);fclose(f);char buffy[2000];sprintf(buffy, %2$cclang -Wall -Wextra -Werror -o %%.*s %%s%2$c, (int)strlen(buff)-2, buff, buff);system(buffy);sprintf(buffy, %2$c./%%.*s%2$c, (int)strlen(buff)-2, buff);system(buffy);}";fprintf(f, a, 10, 34, a, i);fclose(f);char buffy[2000];sprintf(buffy, "clang -Wall -Wextra -Werror -o %.*s %s", (int)strlen(buff)-2, buff, buff);system(buffy);sprintf(buffy, "./%.*s", (int)strlen(buff)-2, buff);system(buffy);}