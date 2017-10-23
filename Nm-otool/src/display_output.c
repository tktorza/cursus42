#include "../includes/nm_tool.h"

char type_n_sect(unsigned int n_sect, t_symtab *symt)
{
    //printf("&&&&&&&&&&&&&&==== %d || %d | %d === %d\n", symt.bss, symt.data, symt.text, n_sect);
    if (n_sect == symt->text)
        return ('T');
    if (n_sect == symt->data)
        return ('D');
    if (n_sect == symt->bss)
        return ('B');
    return ('S');
}

void display_out_64(int value, char *str, char type, t_symtab *symt)
{
    if (value == 0 && ft_strcmp(str, "__mh_execute_header") != 0\
        && type == 'U')
        ft_printf("%16s %c %s\n", " ", type, str);
    else if (symt->exec == 0 && \
        ft_strcmp(str, "__mh_execute_header") != 0)
         ft_printf("%016x %c %s\n", value, type, str);
    else
        ft_printf("00000001%08x %c %s\n", value, type, str);
    //ft_printf("%d %c %s\n", value, type, str);
}

void display_out(int value, char *str, char type, t_symtab *symt)
{
    if (value == 0 && ft_strcmp(str, "__mh_execute_header") != 0\
        && type == 'U')
        ft_printf("%8s %c %s\n", " ", type, str);
    else if (symt->exec == 0 && \
        ft_strcmp(str, "__mh_execute_header") != 0)
         ft_printf("%08x %c %s\n", value, type, str);
    else
        ft_printf("%08x %c %s\n", value, type, str);
    //ft_printf("%d %c %s\n", value, type, str);
}