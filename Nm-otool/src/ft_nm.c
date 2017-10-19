/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   ft_nm.c                                            :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <tktorza@student.42.fr>            +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2017/10/18 13:15:23 by tktorza           #+#    #+#             */
/*   Updated: 2017/10/18 13:37:23 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#include "../includes/nm_tool.h"

#define ERROR(name)                                                                   \
    ft_putstr(ft_strjoin(ft_strjoin("nm: ", name), " No such file or directory.\n")); \
    return (-1)

/*static void get_types_section(t_file **f, uint32_t i,
                              struct section *s, size_t size)
{
    while (i-- > 0)
    {
        if (ft_strcmp(s->sectname, SECT_TEXT) == 0 &&
            ft_strcmp(s->segname, SEG_TEXT) == 0)
            (*f)->sections[TEXT] = (*f)->ns;
        else if (ft_strcmp(s->sectname, SECT_DATA) == 0 &&
                 ft_strcmp(s->segname, SEG_DATA) == 0)
            (*f)->sections[DATA] = (*f)->ns;
        else if (ft_strcmp(s->sectname, SECT_BSS) == 0 &&
                 ft_strcmp(s->segname, SEG_DATA) == 0)
            (*f)->sections[BSS] = (*f)->ns;
        s = (void *)s + size;
        (*f)->ns++;
    }
}*/
//faire un liste tableau de liste des section ||| chaque section est accessible via le numéro de section
char type_n_sect(unsigned int n_sect, t_symtab symt)
{
    //printf("&&&&&&&&&&&&&&==== %d || %d | %d === %d\n", symt.bss, symt.data, symt.text, n_sect);
    if (n_sect == symt.text)
        return ('T');
    if (n_sect == symt.data)
        return ('D');
    if (n_sect == symt.bss)
        return ('B');
    return ('S');
}

char type_element(struct nlist_64 list, struct load_command *lc, t_symtab symt)
{
    char car;

    car = '?';
    if ((list.n_type & N_TYPE) == N_UNDF)
    {
        if (list.n_value)
            car = 'C';
        else
            car = 'U';
    }
    else if ((list.n_type & N_TYPE) == N_ABS)
        car = 'A';
    else if ((list.n_type & N_TYPE) == N_PBUD)
        car = 'U';
    else if ((list.n_type & N_TYPE) == N_SECT)
        car = type_n_sect(list.n_sect, symt);
    else if ((list.n_type & N_TYPE) == N_INDR)
        car = 'I';
    if (!(list.n_type & N_EXT) && car != '?')
        car = ft_tolower(car);
    return (car);
}

void display_out(int value, char *str, char type)
{
    if (value == 0)
        ft_printf("%16s %c %s\n", " ", type, str);
    else
        ft_printf("00000001%08x %c %s\n", value, type, str);
    // ft_printf("%X %c %s\n", value, type, str);
}

void    symtab_building_bis(t_symtab *symt, struct segment_command_64\
     *seg, struct section_64 *sect)
{
    symt->i = 0;
    while (symt->i < seg->nsects)
    {
        if (ft_strcmp(sect->sectname, SECT_TEXT) == 0 &&
            ft_strcmp(sect->segname, SEG_TEXT) == 0)
          {
          
            symt->text = symt->ns;
        }  
              
        else if (ft_strcmp(sect->sectname, SECT_DATA) == 0 &&
                 ft_strcmp(sect->segname, SEG_DATA) == 0)
          {
            //  printf("REPONSE::: %d\n", symt->ns);
            symt->data = symt->ns;
          }       
        else if (ft_strcmp(sect->sectname, SECT_BSS) == 0 &&
                 ft_strcmp(sect->segname, SEG_DATA) == 0)
                 symt->bss = symt->ns;
//               if (g_text != 0 && g_data != 0 && g_bss != 0)
//              break;
        sect = (void *)sect + sizeof(*sect);
        symt->ns++;
        symt->i++;
    }
    //  printf("BUILDING_BIS = %d %d %d %d\n", symt->bss, symt->data, symt->i, symt->ns);
    
}

void    symtab_building(t_symtab *symt, struct mach_header_64 *header,\
     struct load_command *lc)
{
    struct segment_command_64 *seg;
    struct section_64 *sect;

    while (symt->j < header->ncmds)
    {
        if (lc->cmd == LC_SEGMENT_64)
        {
            seg = (struct segment_command_64 *)lc;
            sect = (struct section_64 *)((void *)seg + sizeof(*seg));
            symtab_building_bis(symt, seg, sect);
           // printf("NS:: %d\n", symt->ns);
        }
        lc = (void *)lc + lc->cmdsize;
        symt->j++;
    }
    // printf("BUILDING = %d %d %d %d\n", symt->bss, symt->data, symt->i, symt->text);
}

void print_output(struct symtab_command *sym, char *ptr, struct mach_header_64 *header)
{
    struct load_command *lc;
    char *stringtable;
    struct nlist_64 *array;
    t_symtab symt = {0, 0, 0, -1, 0, 1};

    array = (void *)ptr + sym->symoff;
    stringtable = (void *)ptr + sym->stroff;
    lc = (void *)ptr + sizeof(*header);
    // printf("%d %d %d %d\n", symt.bss, symt.data, symt.i, symt.text);
    array = tri_bulle(stringtable, array, sym->nsyms);
    symtab_building(&symt, header, lc);
    symt.i = -1;
    while (++symt.i < sym->nsyms)
        display_out(array[symt.i].n_value, stringtable + \
            array[symt.i].n_un.n_strx, type_element(array[symt.i], lc, symt));
}

void handle_64(char *ptr)
{
    int ncmds;
    int i;
    struct mach_header_64 *header;
    struct load_command *lc;
    struct symtab_command *sym;

    //converti en header
    header = (struct mach_header_64 *)ptr;
    ncmds = header->ncmds;
    i = 0;
    lc = (void *)ptr + sizeof(*header);
    while (i < ncmds)
    {
        if (lc->cmd == LC_SYMTAB)
        {
            //envoie de liste créee précédemment
            sym = (struct symtab_command *)lc;
            print_output(sym, ptr, header);
            break;
        }
        lc = (void *)lc + lc->cmdsize;
        i++;
    }
}

void handle_32(char *ptr)
{
}

void nm(char *ptr)
{
    int magic_number;
    //on prend le premier octet que l'on convertit en int
    magic_number = *(int *)ptr;
    //regarder si 64bits
    if (magic_number == MH_MAGIC_64)
        handle_64(ptr);
    if (magic_number == MH_MAGIC)
        handle_32(ptr);
}

int main(int ac, char **av)
{
    int fd;
    void *ptr;
    struct stat buf;

    if (ac < 2)
        av[1] = "a.out\0";
    if ((fd = open(av[1], O_RDONLY)) != -1)
    {
        if (fstat(fd, &buf) < 0)
        {
            ERROR("ok");
        }
        if ((ptr = mmap(0, buf.st_size, PROT_READ, MAP_PRIVATE, fd, 0)) == MAP_FAILED)
        {
            ERROR("ok");
        }
        nm(ptr);
    }
    else
        ERROR(av[1]);
    return 0;
}