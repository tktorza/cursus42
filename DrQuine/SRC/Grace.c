#include <stdio.h>

#define WRITE_INTO FILE *file = fopen("Grace_kid.c", "w");char *s1 = "#include <stdio.h>%c%c#define WRITE_INTO FILE *file = fopen(%cGrace_kid.c%c, %cw%c);char *s1 = %c%s%c;";char *s2 = "char *s2 = %c%s%c;fprintf(file, s1, 10, 10, 34, 34, 34, 34, 34, s1, 34);fprintf(file, s2, 34, s2, 34, 10, 10, 10, 10, 10);fclose(file);%c#define MAIN int main(){WRITE_INTO return (0);}%c/*%c    try to use macros%c*/%cMAIN";fprintf(file, s1, 10, 10, 34, 34, 34, 34, 34, s1, 34);fprintf(file, s2, 34, s2, 34, 10, 10, 10, 10, 10);fclose(file);
#define MAIN int main(){WRITE_INTO return (0);}
/*
    try to use macros
*/
MAIN