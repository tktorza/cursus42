#include "../../includes/nm_tool.h"

void    display_text_32(t_symtab *symt, struct section *sect,
    struct mach_header *header)
{
    if (((symt->bonus & BSS) == 0 && (symt->bonus & DATA) == 0) ||
        (symt->bonus & ALL) != 0)
    {
        ft_putstr("Contents of (__TEXT,__text) section\n");
        print_res(sect->addr, sect->size, (char *)header + sect->offset);
        symt->lib == 1 ? 0 : write(1, "\n", 1);
    }
    symt->text = symt->ns;   
}

void    display_data_32(t_symtab *symt, struct section *sect,
    struct mach_header *header)
{
    if (((symt->bonus & DATA) != 0) ||
        (symt->bonus & ALL) != 0)
    {
        ft_putstr("Contents of (__DATA,__data) section\n");
        print_res(sect->addr, sect->size, (char *)header + sect->offset);
        symt->lib == 1 ? 0 : write(1, "\n", 1);
    }
    symt->data = symt->ns;   
}

void    display_bss_32(t_symtab *symt, struct section *sect,
    struct mach_header *header)
{
    if ((symt->bonus & BSS) != 0 ||
        (symt->bonus & ALL) != 0)
    {
        ft_putstr("Contents of (__BSS,__bss) section\n");
        print_res(sect->addr, sect->size, (char *)header + sect->offset);
        symt->lib == 1 ? 0 : write(1, "\n", 1);
    }
    symt->bss = symt->ns;   
}