#include <stdio.h>
#define NL 10
#define ST 42
#define BS 47

void empty(void){}
/*
    Ci-dessous la fonction main:
*/
int main()
{
/*
    Nous sommes au sein de la fonction main...
*/
char *s1 = "#include <stdio.h>%c#define NL 10%c#define ST 42%c#define BS 47%c%cvoid empty(void){}%c%c%c%c    Ci-dessous la fonction main:%c%c%c%cint main()%c{%c%c%c%c    Nous sommes au sein de la fonction main...%c%c%c%cchar %cs1 = %c%s%c;%c";
char *s2 = "char *s2 = %c%s%c;%cempty();%cprintf(s1, 10, 10, 10, 10, 10, 10, BS, ST, 10, 10, ST, BS, 10, 10, 10, BS, ST, 10, 10, ST, BS, 10, ST, 34, s1, 34, 10);%cprintf(s2, 34, s2, 34, 10, 10, 10, 10, 10);%creturn (0);%c}";
empty();
printf(s1, 10, 10, 10, 10, 10, 10, BS, ST, 10, 10, ST, BS, 10, 10, 10, BS, ST, 10, 10, ST, BS, 10, ST, 34, s1, 34, 10);
printf(s2, 34, s2, 34, 10, 10, 10, 10, 10);
return (0);
}