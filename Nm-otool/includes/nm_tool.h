/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   nm_tool.h                                          :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <tktorza@student.42.fr>            +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2017/10/03 12:02:15 by tktorza           #+#    #+#             */
/*   Updated: 2017/10/18 13:49:03 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#ifndef NM_TOOL_H
# define NM_TOOL_H

# include <fcntl.h>
# include <stdio.h>
# include <sys/mman.h>
# include <mach-o/loader.h>
# include <mach-o/nlist.h>
# include <sys/stat.h>
# include <stdlib.h>
# include "../libft/inc/libft.h"
# include "../libft/inc/ft_printf.h"

int g_data;
int g_bss;
int g_text;



typedef struct          s_symtab
{
    int data;
    int bss;
    int text;
    int i;
    int j;
    int ns;
    int exec;
}                       t_symtab;


void nm(char *ptr, t_symtab *symt);
int main(int ac, char **av);

struct nlist     *tri_bulle(char *stringtable, struct nlist *tab,
    int taille);
struct nlist_64     *tri_bulle_64(char *stringtable, struct nlist_64 *tab,
    int taille);
    
void handle_32(char *ptr, t_symtab *symt);

void handle_64(char *ptr, t_symtab *symt);

void display_out_64(int value, char *str, char type, t_symtab *symt);
void display_out(int value, char *str, char type, t_symtab *symt);
char type_n_sect(unsigned int n_sect, t_symtab *symt);

int                 ft_printf(const char *str, ...);

#endif
