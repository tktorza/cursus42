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
# include <mach-o/ranlib.h>
# include <mach-o/fat.h>
# include <ar.h>
# include <sys/stat.h>
# include <stdlib.h>
# include "../libft/inc/libft.h"
# include "../libft/inc/ft_printf.h"

int g_data;
int g_bss;
int g_text;



typedef struct          s_symtab
{
    uint32_t data;
    uint32_t bss;
    uint32_t text;
    uint32_t i;
    uint32_t j;
    uint32_t ns;
    int exec;
    int otool;
}                       t_symtab;

typedef struct			s_offlist
{
	uint32_t			off;
	uint32_t			strx;
	struct s_offlist	*next;
}						t_offlist;

int ft_nm(char *av);
int type_bin(char *ptr, char *file, t_symtab *symt);
int main(int ac, char **av);

struct nlist     *tri_bulle(char *stringtable, struct nlist *tab,
    uint32_t taille);
struct nlist_64     *tri_bulle_64(char *stringtable, struct nlist_64 *tab,
    uint32_t taille);
    
    
void handle_32(char *ptr, t_symtab *symt);

void handle_64(char *ptr, t_symtab *symt);

void handle_lib(char *ptr, char *file, t_symtab *symt);

void handle_fat(char *ptr, char * file, t_symtab *symt);


void display_out_64(struct nlist_64 elem, char *str, char type);
void display_out(struct nlist elem, char *str, char type);
char type_n_sect(unsigned int n_sect, t_symtab *symt);

int                 ft_printf(const char *str, ...);

#endif
