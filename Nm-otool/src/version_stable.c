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
char type_n_sect(unsigned int n_sect, struct load_command *lc)
{
    if (n_sect == g_text)
        return ('T');
    if (n_sect == g_data)
        return ('D');
    if (n_sect == g_bss)
        return ('B');
    return ('S');
}

char type_element(struct nlist_64 list, struct load_command *lc)
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
        car = type_n_sect(list.n_sect, lc);
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

void print_output(struct symtab_command *sym, char *ptr, struct mach_header_64 *header)
{
    int nsect;
    int ncmds;
    struct segment_command_64 *seg;
    struct section_64 *sect;
    char car;
    struct load_command *lc;
    char *stringtable;
    struct nlist_64 *array;
    t_symtab symt = {0, 0, 0, 0, 0, 1};


    array = (void *)ptr + sym->symoff;
    stringtable = (void *)ptr + sym->stroff;
    ncmds = header->ncmds;
    lc = (void *)ptr + sizeof(*header);

    array = tri_bulle(stringtable, array, sym->nsyms);
    while (symt.i < sym->nsyms)
    {
        // printf("nsect = %d", nsect);
        while (symt.j < header->ncmds)
        {
            if (lc->cmd == LC_SEGMENT_64)
            {
                //remplir une liste
                seg = (struct segment_command_64 *)lc;
                sect = (struct section_64 *)((void *)seg + sizeof(*seg));
                // ft_printf("segname : %s | nsect: %s\n", sect->segname, sect->sectname);
                for (int f = 0; f < seg->nsects; ++f)
                {
                    if (ft_strcmp(sect->sectname, SECT_TEXT) == 0 &&
                        ft_strcmp(sect->segname, SEG_TEXT) == 0)
                        g_text = symt.ns;
                    else if (ft_strcmp(sect->sectname, SECT_DATA) == 0 &&
                             ft_strcmp(sect->segname, SEG_DATA) == 0)
                        g_data = symt.ns;
                    else if (ft_strcmp(sect->sectname, SECT_BSS) == 0 &&
                             ft_strcmp(sect->segname, SEG_DATA) == 0)
                        g_bss = symt.ns;
                //    ft_printf("sect->segname: %s | sect : %s | \n", sect->segname, sect->sectname);
                   if (g_text != 0 && g_data != 0 && g_bss != 0)
                    break;
                    sect = (void *)sect + sizeof(*sect);
                    symt.ns++;
                }
            }
            lc = (void *)lc + lc->cmdsize;
            symt.j++;
        }
        car = type_element(array[symt.i], lc);
          display_out(array[symt.i].n_value, stringtable + array[symt.i].n_un.n_strx, car);
          symt.i++;
    }
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